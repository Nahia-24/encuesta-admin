<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Notifications\invoicePaid;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use App\Http\Controllers\PDFController;
use App\Models\EventAssistant;

class PaypalController extends Controller
{
    //
    public function create(Request $request){

        $data=json_decode($request->getContent(),true);

        //init paypal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token=$provider->getAccessToken();
        $provider->setAccessToken($token);
        
       
        
        $order=$provider->createOrder([
            "intent"=>"CAPTURE",
            "purchase_units"=> [
                [
                    "amount" => [
                    "currency_code"=>"USD",
                    "value"=> $data['quantity']
                    ],
                    "description" => $data['description']
                    ]
            ]
            ]);
        //save create order to database
        $pedido=Order::create([
           'nombres'=>$data['name'],
           'apellidos'=>$data['lastName'],
           'cedula'=>$data['id'],
           'telefono'=>$data['phone'],
           'reference_number'=>$order['id'],
           'description'=>$data['description'],
           'precio'=>$data['quantity'],
           'status'=>$order['status'],
        ]);
       
        $this-> makeOrderNotifications($pedido);

        return response()->json($order);
    }


    public function capture(Request $request){

        $data=json_decode($request->getContent(),true);
        $orderId=$data['orderId'];
       

        //init paypal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token=$provider->getAccessToken();
        $provider->setAccessToken($token);

        $result=$provider->capturePaymentOrder($orderId);

        //update database
        if ($result['status'] === 'COMPLETED') {
            DB::table('orders')
                ->where('reference_number', $result['id'])
                ->update(['status' => 'COMPLETED', 'updated_at' => \Carbon\Carbon::now()]);

            $eventAsistant = EventAssistant::find($data['event_assistant_id']);
            if ($eventAsistant && $eventAsistant->isFullyPaid()) {
                $eventAsistant->is_paid = true;
                $eventAsistant->save();
            }

            $PDFController = new PDFController();
            $meta = $PDFController->buildPDF_Mail($data['event_assistant_id']);

            return response()->json(['status' => 'COMPLETED', 'meta' => $meta]);
        }

        // Si llega aquí, es porque no se completó
        return response()->json(['status' => $result['status'], 'message' => 'El pago no fue completado.']);
    }


    public function makeOrderNotifications($order){

   
        $user=User::whereHas('roles', function ($query) {
        $query->where('name', 'Admin');
    })->get();

        $user->except($order->nombre);
        $user->each(function(User $user) use ($order){
        $user->notify(new invoicePaid($order));
     });

    }


    public function Paypal($eventAssistantId)
{
    $eventAssistant = EventAssistant::with('user')->findOrFail($eventAssistantId);

    $pago = [
        'event_assistant_id' => $eventAssistant->id,
        'user_name' => $eventAssistant->user->name ?? '',
        'amount' => $eventAssistant->amount ?? 0, 
        'email' => $eventAssistant->user->email ?? '',
        'description' => 'Pago de entrada al evento', 
    ];

    return view('paypal.checkout', compact('pago'));
}



}
