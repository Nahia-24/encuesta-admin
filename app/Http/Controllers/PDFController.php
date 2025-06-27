<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Mail\EnvioNotificacionTickenGenerado;
use App\Mail\PruebaEmail;
use Pdf;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PDFController extends Controller
{

    public function enviarEmailticket($meta)
	{

	   Mail::to($meta['email'])->send(new EnvioNotificacionTickenGenerado($meta));



	}

}
