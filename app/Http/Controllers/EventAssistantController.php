<?php

namespace App\Http\Controllers;

use App\Exports\EventAssistantsExport;
use App\Exports\TemplateExport;
use App\Imports\AssistantsImport;
use App\Models\AdditionalParameter;
use App\Models\Departament;
use App\Models\Event;
use App\Models\eventAssistant;
use App\Models\FeatureConsumption;
use App\Models\Payment;
use App\Models\TicketFeatures;
use App\Models\TicketType;
use App\Models\User;
use App\Models\UserEventParameter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PDFController;
use App\Models\Coupon;

class EventAssistantController extends Controller
{
    public function index(Request $request, $idEvent)
    {
        $event = Event::findOrFail($idEvent);

        // Número de asistentes registrados para el evento
        $totalTickets = eventAssistant::where('event_id', $idEvent)->where('has_entered', true)->count();
        $tickets = TicketType::where('event_id', $idEvent)->get();

        // Capacidad total del evento
        $capacity = $event->capacity;

        // Calcula los datos para el gráfico
        $availableTickets = $capacity - $totalTickets;
        $dataGeneral = [
            'soldTickets' => $totalTickets, // Entradas vendidas
            'availableTickets' => $availableTickets, // Entradas disponibles
            'capacity' => $capacity // Capacidad total
        ];


        // Obtener todos los tipos de tickets para el evento
        $ticketTypes = TicketType::where('event_id', $idEvent)->get();

        // Inicializar el array para almacenar la información
        $ticketsInfo = [];

        foreach ($ticketTypes as $ticketType) {
            // Contar cuántos usuarios han ingresado con este tipo de ticket
            $totalEntered = eventAssistant::where('event_id', $idEvent)
                ->where('ticket_type_id', $ticketType->id)
                ->where('has_entered', true)
                ->count();

            // Calcular entradas disponibles
            $availableTickets = $ticketType->capacity - $totalEntered;

            // Guardar la información en el array
            $ticketsInfo[] = [
                'ticket_type_id' => $ticketType->id,
                'name' => $ticketType->name,
                'price' => $ticketType->price,
                'capacity' => $ticketType->capacity,
                'soldTickets' => $totalEntered,
                'availableTickets' => $availableTickets,
            ];
        }
        // Aplicar búsqueda y paginación
        $query = eventAssistant::with([
            'user',
            'guardian',
            'ticketType',
            'department',
            'eventParameters' => function ($query) use ($idEvent) {
                $query->where('event_id', $idEvent);
            }
        ])->where('event_id', $idEvent);

        if ($request->has('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                // Buscar en la relación 'user'
                $q->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });

                // Buscar en la relación 'ticketType'
                $q->orWhereHas('ticketType', function ($query2) use ($search) {
                    $query2->where('name', 'like', "%{$search}%");
                });

                // Verificar si 'search' contiene "Entrada" y filtrar por 'has_entered'
                if (strtolower($search) === 'entrada') {
                    $q->orWhere('has_entered', 1); // Buscar entradas con valor 1
                } elseif (strtolower($search) === 'no entrada') {
                    $q->orWhere('has_entered', 0); // Buscar entradas con valor 0
                }
            });
        }

        $asistentes = $query->paginate(10);

        return view('eventAssistant.index', compact(['asistentes', 'idEvent', 'dataGeneral', 'event', 'ticketsInfo', 'tickets']));
    }

    // Muestra la vista para subir el archivo de Excel
    public function showMassAssign($idEvent)
    {
        $event = Event::findOrFail($idEvent);
        return view('eventAssistant.massAssign', compact('event'));
    }

    // Procesa el archivo de Excel y asigna los asistentes de forma masiva
    public function uploadMassAssign(Request $request, $idEvent)
    {
        if ($this->eventoFinalizado($idEvent)) {
            return redirect()->back()
                ->with('messages', 'No se puede realizar está acción porque el evento ya ha sido finalizado');
        }
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048', // Validar que sea un archivo Excel
        ]);

        try {
            // Instanciar el importador y recolectar los datos de las importaciones
            $import = new AssistantsImport($idEvent);
            Excel::import($import, $request->file('file'));

            // Obtener los detalles de los usuarios agregados y las novedades
            $importedUsers = $import->getImportedUsers();
            $messages = $import->getMessages();

            return redirect()->route('eventAssistant.massAssign', $idEvent)
                ->with('success', 'Asistentes asignados exitosamente.')
                ->with('importedUsers', $importedUsers)
                ->with('messages', $messages);
        } catch (\Exception $e) {
            return redirect()->route('eventAssistant.massAssign', $idEvent)
                ->with('error', 'Hubo un error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function singleAssignForm($idEvent)
    {
        $assistants = User::whereHas('roles', function ($query) {
            $query->where('name', 'Assistant');
        })->get();
        $event = Event::findOrFail($idEvent);
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        return view('eventAssistant.singleAssign', compact('event', 'assistants', 'ticketTypes'));
    }

    public function uploadSingleAssign(Request $request, $eventId)
    {
        if ($this->eventoFinalizado($eventId)) {
            return redirect()->route('eventAssistant.index', $eventId)
                ->with('success', 'No se puede realizar está acción porque el evento ya ha sido finalizado.');
        }
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'ticketTypes' => 'nullable|array',
            'ticketTypes.*' => 'nullable|exists:ticket_types,id',
        ]);

        // Obtener el evento
        $event = Event::findOrFail($eventId);
        // return $request;
        foreach ($validatedData['ticketTypes'] as $userId => $ticketId) {
            // Buscar si ya existe un registro para este usuario y evento
            $eventAssistant = EventAssistant::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->first();

            if ($eventAssistant) {
                // Actualizar el registro existente con el tipo de ticket seleccionado
                $eventAssistant->ticket_type_id = $validatedData['ticketTypes'][$userId] ?? null;
                $eventAssistant->save();
            } else {
                // Crear un nuevo registro
                EventAssistant::create([
                    'event_id' => $eventId,
                    'user_id' => $userId,
                    'ticket_type_id' => $validatedData['ticketTypes'][$userId] ?? null,
                    'has_entered' => false, // Asumimos que inicialmente el usuario no ha entrado al evento
                ]);
            }
        }

        // Redirigir con un mensaje de éxito
        return redirect()->route('eventAssistant.index', $eventId)
            ->with('success', 'Asistentes asignados exitosamente.');
    }

    public function singleCreateForm($idEvent)
    {
        $event = Event::findOrFail($idEvent);

        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        // Obtener el evento por su ID
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        // Obtener asistentes mayores de 18 años con número de documento
        $guardians = EventAssistant::where('event_id', $idEvent)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('document_number')
                    ->where('birth_date', '<=', now()->subYears(18))
                ; // Filtrar mayores de 18
            })
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'document_number'); // Seleccionar solo los campos requeridos
            }])
            ->get();
        return view('eventAssistant.singleCreate', compact('event', 'departments', 'ticketTypes', 'additionalParameters', 'guardians'));
    }

    public function singleCreateUpload(Request $request, $idEvent)
    {
        if ($this->eventoFinalizado($idEvent)) {
            return redirect()->back()
                ->with('success', 'No se puede realizar esta acción porque el evento ya ha sido finalizado.');
        }

        $event = Event::find($idEvent);

        $registrationParameters = json_decode($event->registration_parameters, true) ?? [];

        // Reglas de validación
        $validationRules = [];
        foreach ($registrationParameters as $param) {
            switch ($param) {
                case 'name':
                case 'lastname':
                    $validationRules[$param] = 'required|string|max:255';
                    break;
                case 'email':
                    $validationRules[$param] = 'required|email|max:255';
                    break;
                case 'type_document':
                    $validationRules[$param] = 'required|string|max:3';
                    break;
                case 'document_number':
                    $validationRules[$param] = 'required|string|max:20';
                    break;
                case 'phone':
                    $validationRules[$param] = 'nullable|string|max:15';
                    break;
                case 'department_id':
                    $validationRules[$param] = 'nullable|exists:departments,id';
                    break;
                case 'city_id':
                    $validationRules[$param] = 'nullable|exists:cities,id';
                    break;
                case 'birth_date':
                    $validationRules[$param] = 'nullable|date';
                    break;
            }
        }


        $validatedData = $request->validate($validationRules);

        // Buscar usuario existente
        $user = null;
        if ($request->has('email') || $request->has('document_number')) {

            $user = User::where('email', $request->email)
                ->orWhere('document_number', $request->document_number)
                ->first();
        }

        // Si no existe, crearlo
        if (!$user) {
            $userFillableColumns = (new User())->getFillable();
            $createData = [];

            foreach ($userFillableColumns as $column) {
                if ($request->has($column)) {
                    $createData[$column] = $request[$column];
                }
            }

            $createData['status'] = false;
            $user = User::create($createData);
            // Asignar rol solo si es nuevo
            $assistantRole = Role::firstOrCreate(['name' => 'assistant']);
            $user->assignRole($assistantRole);
        }
        // Asociar en tabla intermedia
        $guardianId = $request->input('guardian_id') ?? null;

        EventAssistant::firstOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'ticket_type_id' => $request['id_ticket'] ?? null,
                'has_entered' => false,
                'guardian_id' => $guardianId ?? null,
                'department_id' => $request['department_id'] ?? null, 
            ]
        );

        // Agregar parámetros adicionales si aplica
        $definedParameters = AdditionalParameter::where('event_id', $event->id)->get();

        $userFillableColumns = (new User())->getFillable();
        $additionalParameters = $request->except(array_merge(['_token'], $userFillableColumns));

        foreach ($definedParameters as $definedParameter) {
            if (isset($additionalParameters[$definedParameter->name])) {
                UserEventParameter::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'additional_parameter_id' => $definedParameter->id,
                    'value' => $additionalParameters[$definedParameter->name],
                ]);
            }
        }

        return redirect()->route('eventAssistant.singleCreateForm', $idEvent)
            ->with('success', 'Inscripción exitosa.');
    }


    public function edit($idEventAssistant)
    {
        $eventAssistant = EventAssistant::findOrFail($idEventAssistant);
        $userEventParameter = UserEventParameter::where('user_id', $eventAssistant->user_id)->where('event_id', $eventAssistant->event_id)->get();
        // return $userEventParameter[0]->value;
        $event = Event::findOrFail($eventAssistant->event_id);
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $currentCity = $eventAssistant->user->city ?? null;
        $event = Event::findOrFail($event->id);
        $ticketTypes  = TicketType::where('event_id', $event->id)->get();
        // Obtener asistentes mayores de 18 años con número de documento
        $guardians = EventAssistant::where('event_id', $eventAssistant->event_id)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('document_number')
                    ->where('birth_date', '<=', now()->subYears(18))
                ; // Filtrar mayores de 18
            })
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'document_number'); // Seleccionar solo los campos requeridos
            }])
            ->get();
        return view('eventAssistant.editAssistant', compact('event', 'departments', 'ticketTypes', 'additionalParameters', 'eventAssistant', 'userEventParameter', 'guardians','currentCity'));
    }

    public function singleUpdateUpload(Request $request, $idEventAssistant)
    {
        $eventAssistant = EventAssistant::find($idEventAssistant);
        $idEvent = $eventAssistant->event_id;

        if ($this->eventoFinalizado($idEvent)) {
            return redirect()->back()
                ->with('success', 'No se puede realizar esta acción porque el evento ya ha sido finalizado.');
        }

        // Obtener el evento y el usuario asociados
        $event = Event::findOrFail($idEvent);

        $user = User::findOrFail($eventAssistant->user_id);
        // ⚠️ OMITIR la actualización del usuario
        // $updateData = [];
        // $userFillableColumns = (new User())->getFillable();
        // foreach ($userFillableColumns as $column) {
        //     if ($request->has($column)) {
        //         $updateData[$column] = $request[$column];
        //     }
        // }
        // $user->update($updateData);

        $guardianId = $request->input('guardian_id') ?? null;

        // ✅ Actualizar solo la información del asistente
        $eventAssistant->update([
            'ticket_type_id' => $request['id_ticket'] ?? null,
            'guardian_id' => $guardianId,
        ]);

        // ✅ Actualizar o crear parámetros adicionales
        $definedParameters = AdditionalParameter::where('event_id', $event->id)->get();

        $userFillableColumns = (new User())->getFillable();

        $additionalParameters = $request->except(array_merge(['_token'], $userFillableColumns));
        foreach ($definedParameters as $definedParameter) {
            if (isset($additionalParameters[$definedParameter->name])) {

                UserEventParameter::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'additional_parameter_id' => $definedParameter->id,
                    ],
                    [
                        'value' => $additionalParameters[$definedParameter->name],
                    ]
                );
            }
        }


        return redirect()->route('eventAssistant.index', $idEvent)
            ->with('success', 'Actualización exitosa.');
    }


    public function singleDelete($idEventAssistant)
    {
        // Buscar el registro de EventAssistant por el ID proporcionado
        $eventAssistant = EventAssistant::find($idEventAssistant);
        // Verificar si el registro existe
        if (!$eventAssistant) {
            return redirect()->back()->with('error', 'El registro no fue encontrado.');
        }
        // Validar que has_entered no sea 1
        if ($eventAssistant->has_entered == 1) {
            return redirect()->back()->with('error', 'No se puede eliminar el registro porque el asistente ya ha ingresado.');
        }
        // Verificar si existen registros relacionados en feature_consumptions
        $relatedFeatureConsumptions = FeatureConsumption::where('event_assistant_id', $idEventAssistant)->exists();
        if ($relatedFeatureConsumptions) {
            return redirect()->back()->with('error', 'No se puede eliminar el registro porque tiene consumos de características asociados.');
        }
        try {
            // Intentar eliminar el registro
            $eventAssistant->delete();

            // Redirigir de vuelta con un mensaje de éxito
            return redirect()->route('eventAssistant.index')->with('success', 'El registro fue eliminado exitosamente.');
        } catch (\Exception $e) {
            // Manejo de errores, redirigir de vuelta con un mensaje de error
            return redirect()->back()->with('error', 'Ocurrió un error al intentar eliminar el registro.');
        }
    }

    public function showQr($id)
    {
        // Obtener el asistente por ID
        $asistente = EventAssistant::findOrFail($id);

        // Retornar una vista que muestre el QR
        return view('eventAssistant.qr', compact('asistente'));
    }

    public function infoQr($id, $public_link)
    {
        // Buscar el asistente por su ID
        $eventAssistant = EventAssistant::findOrFail($id);

        if ($eventAssistant->guid != $public_link) {
            abort(404); // Devuelve un error 404 Not Found
        }
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar si el usuario tiene los roles 'admin' o 'organizer'
            if ($user->hasRole('admin') || $user->hasRole('organizer')) {
                // Redirigir a la vista con información completa
                return view('eventAssistant.qr.adminView', compact('eventAssistant'));
            } else {
                // Redirigir a la vista con información básica para usuarios autenticados que no tienen esos roles
                return view('eventAssistant.qr.basicAuthView', compact('eventAssistant'));
            }
        } else {
            // Redirigir a la vista con información básica para usuarios no autenticados
            return view('eventAssistant.qr.guestView', compact('eventAssistant'));
        }
    }

    public function registerEntry($id)
    {
        // Buscar el asistente por su ID
        $eventAssistant = EventAssistant::findOrFail($id);

        // Cambiar el estado de has_entered
        $eventAssistant->has_entered = true;
        $eventAssistant->entry_time = now();
        $eventAssistant->save();

        $event = $eventAssistant->event;
        $ticketType = $eventAssistant->ticketType; // Obtener el tipo de ticket
        $successMessage = 'Ingreso registrado correctamente.';

        // Contar cuántas entradas han ingresado con este tipo de ticket
        $currentTicketCount = EventAssistant::where('event_id', $event->id)
            ->where('ticket_type_id', $ticketType->id)
            ->where('has_entered', true)
            ->count();

        // Verificar el porcentaje de aforo del tipo de ticket
        $ticketCapacityReached = $currentTicketCount >= $ticketType->capacity;
        $ticketNearCapacity = $currentTicketCount >= ($ticketType->capacity * 0.9);

        if ($ticketNearCapacity && !$ticketCapacityReached) {
            // Avisar que se está cerca del aforo máximo para el tipo de ticket
            return redirect()->back()->with([
                'success' => $successMessage,
                'warning' => 'Atención: Se ha alcanzado el 90% de la capacidad para el tipo de Boleta "' . $ticketType->name . '". Se han registrado ' . $currentTicketCount . " entradas y la capacidad máxima es de " . $ticketType->capacity . "."
            ]);
        } elseif ($ticketCapacityReached) {
            // Redirigir con una alerta si se ha superado el aforo para el tipo de ticket
            return redirect()->back()->with([
                'success' => $successMessage,
                'error' => 'Aforo máximo alcanzado o superado para el tipo de Boleta "' . $ticketType->name . '". Se han registrado ' . $currentTicketCount . " entradas y la capacidad máxima es de " . $ticketType->capacity . "."
            ]);
        } else {
            return redirect()->back()->with('success', $successMessage);
        }
    }

    public function rejectEntry($id)
    {
        // Buscar el asistente por su ID
        $eventAssistant = EventAssistant::findOrFail($id);

        // Cambiar el estado de rechazo
        $eventAssistant->rejected = true;
        $eventAssistant->rejected_time = now();
        $eventAssistant->save();

        // Redirigir de nuevo a la vista con un mensaje de rechazo
        return redirect()->back()->with('success', 'Ingreso rechazado correctamente.');
    }

    public function generatePDF($id)
    {
        $asistente = EventAssistant::with('user', 'event')->findOrFail($id);
        $evento = Event::find($asistente->event_id);

        // Generar QR code como base64
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($asistente->qrCode);

        $pdf = Pdf::loadView('pdf.assistant', compact('asistente', 'evento', 'qrCodeBase64'));

        //se muestra en una nueva ventana
        return $pdf->stream('asistente_' . $asistente->user->name . '_evento_' . $evento->name . '.pdf');
        // Se descarga,
        // return $pdf->download('Asistente_Evento_' . $asistente->user->name . '.pdf');
    }

    public function consumeFeature(EventAssistant $eventAssistant, $feature)
    {
        $eventAssistant = EventAssistant::find($eventAssistant)->first();
        // return $eventAssistant;
        $feature = TicketFeatures::find($feature);
        // return $feature->id;
        $featureConsumptions = FeatureConsumption::where('event_assistant_id', $eventAssistant->id)->where('ticket_feature_id', $feature->id)->get()->count();
        // Verificar si el feature es consumible y no ha sido consumido
        if (!$feature->consumable || $featureConsumptions > 0) {
            return redirect()->back()->with('error', 'El feature no es consumible o ya ha sido consumido.');
        }

        // Marcar el feature como consumido
        $featureConsumptions = new FeatureConsumption();
        $featureConsumptions->event_assistant_id = $eventAssistant->id;
        $featureConsumptions->ticket_feature_id = $feature->id;
        $featureConsumptions->consumed_at = now();
        $featureConsumptions->save();

        return redirect()->back()->with('success', 'El feature ha sido consumido exitosamente.');
    }

    public function downloadTemplate($id)
    {
        // Obtener los parámetros de inscripción (registration_parameters) del evento desde la base de datos
        $event = Event::findOrFail($id);

        // Decodificar los parámetros de inscripción de formato JSON a un array PHP
        $registration_parameters = json_decode($event->registration_parameters, true);

        // Si registration_parameters es nulo o vacío, usa un conjunto predeterminado
        if (empty($registration_parameters)) {
            $registration_parameters = [
                'name',
                'lastname',
                'email',
                'type_document',
                'document_number',
                'phone',
                'city_id',
                'birth_date',
            ];
        }

        // Agregar la columna adicional "ticket_type"
        $registration_parameters[] = 'ticket_type';

        // Crear una instancia de la exportación con los encabezados
        $export = new TemplateExport($registration_parameters);

        // Descargar el archivo Excel
        return Excel::download($export, 'plantilla_asistentes-' . $event->name . '.xlsx');
    }

    public function specificSearch($idEvent)
    {
        $eventAssistant = EventAssistant::where('event_id', $idEvent)->get();
        $event = Event::find($idEvent);
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $event = Event::findOrFail($idEvent);
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        return view('eventAssistant.specificSearch', compact('event', 'departments', 'ticketTypes', 'additionalParameters'));
    }

    public function specificSearchUploead(Request $request, $idEvent)
    {
        // Obtener los campos de búsqueda del request y eliminar aquellos con valores nulos
        $input = array_filter($request->except('_token'), function ($value) {
            return !is_null($value); // Filtra inputs que no sean null
        });

        // Obtener los IDs de usuarios que ya están registrados como asistentes del evento
        $eventAssistantUserIds = EventAssistant::where('event_id', $idEvent)->pluck('user_id');

        // Buscar en la tabla Users, pero solo aquellos que estén asociados con el evento
        $query = User::whereIn('id', $eventAssistantUserIds);
        $query->where('id', -1);
        foreach ($input as $key => $value) {
            if ($value) {
                $query->orWhere($key, 'LIKE', '%' . $value . '%');
            }
        }

        // Ejecutar la búsqueda en la tabla Users
        $users = $query->get();
        // Si no se encontraron usuarios en la tabla User, buscar en UserEventParameter y AdditionalParameter
        if ($users->isEmpty()) {
            $additionalParameterMatches = [];

            foreach ($input as $key => $value) {
                if ($value) {
                    // Encontrar los additional_parameters que coincidan con los nombres de los campos
                    $parameters = AdditionalParameter::where('event_id', $idEvent)
                        ->where('name', $key)
                        ->get();

                    foreach ($parameters as $parameter) {
                        // Buscar en UserEventParameter por el valor correspondiente y usuarios relacionados con el evento
                        $matches = UserEventParameter::where('event_id', $idEvent)
                            ->whereIn('user_id', $eventAssistantUserIds)
                            ->where('additional_parameter_id', $parameter->id)
                            ->where('value', 'LIKE', '%' . $value . '%')
                            ->pluck('user_id');

                        // Agregar los matches al arreglo de resultados
                        $additionalParameterMatches = array_merge($additionalParameterMatches, $matches->toArray());
                    }
                }
            }

            // Obtener los usuarios que coinciden en UserEventParameter y están relacionados con el evento
            $users = User::whereIn('id', $additionalParameterMatches)->get();
        }

        // Retornar la vista con los resultados
        $eventAssistant = EventAssistant::where('event_id', $idEvent)->get();
        $event = Event::find($idEvent);
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $event = Event::findOrFail($idEvent);
        $ticketTypes  = TicketType::where('event_id', $idEvent)->get();
        // return view('eventAssistant.specificSearch', compact('users', 'event'));
        return view('eventAssistant.specificSearch', compact('event', 'departments', 'ticketTypes', 'additionalParameters', 'users'));
    }

    public function exportExcel(Request $request, $idEvent)
    {
        $event = Event::find($idEvent);
        // Obtener la búsqueda, campos seleccionados y parámetros adicionales desde el request
        $search = $request->input('search');
        $selectedFields = json_decode($event->registration_parameters, true) ?? [];
        $additionalParameters = $event->additionalParameters;
        // Exportar el archivo Excel usando los datos proporcionados
        return Excel::download(
            new EventAssistantsExport($idEvent, $selectedFields, $additionalParameters, $search),
            'asistentes_de_' . $event->name . '_' . date('d-m-Y') . '.xlsx'
        );
    }

    public function sendMsg($idEvent)
    {
        $eventAssistants = EventAssistant::where('event_id', $idEvent)->get();
        // Obtener todos los asistentes del evento
        $eventAssistants = EventAssistant::where('event_id', $idEvent)->get();

        foreach ($eventAssistants as $eventAssistant) {
            // Generar la URL con la ruta nombrada 'eventAssistant.infoQr'
            $url = route('eventAssistant.infoQr', ['id' => $eventAssistant->id, 'guid' => $eventAssistant->guid]);

            // Construir el mensaje de texto
            $message = "La URL para que puedas acceder al evento es la siguiente: $url";

            // Aquí puedes utilizar un servicio de SMS para enviar el mensaje
            // Ejemplo de uso de un servicio de SMS (Twilio, Nexmo, etc.)
            // SmsService::send($assistant->user->phone, $message);

            // Mostrar en consola o logs para depuración
            info("Mensaje enviado a {$eventAssistant->user->phone}: $message");
        }
    }

    public function sendEmail($id)
    {
        // Buscar el registro de EventAssistant por ID
        $eventAssistant = EventAssistant::find($id);

        // Asegúrate de que el asistente existe
        if (!$eventAssistant || !$eventAssistant->user) {
            return response()->json(['message' => 'No se ha encontrado el usuario respectivo'], 404);
        }

        // Obtener el correo electrónico del usuario relacionado
        $email = $eventAssistant->user->email;
        // return $email;

        // Enviar el correo
        Mail::send(
            'emails.assistant',
            ['eventAssistant' => $eventAssistant],
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Información del evento');
            }
        );

        return response()->json(['message' => 'Email enviado a ' . $email]);
    }

    public function eventoFinalizado($idEvent)
    {
        return Event::find($idEvent)->status == 4 ? true : false;
    }

    public function payment($id)
    {
        $eventAssistant = EventAssistant::find($id);
        return view('eventAssistant.payment', compact('eventAssistant'));
    }
    public function paymentStore(Request $request)
    {
        $request->validate([
            'payer_name' => 'required|string|max:255',
            'payer_document_type' => 'required|string|max:10',
            'payer_document_number' => 'required|string|max:50',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_proof' => 'nullable|image|max:2048',
            'courtesy_code' => 'nullable|string|max:50',
        ]);

        $eventAssistant = EventAssistant::find($request->event_assistant_id);

        // Si tiene código de cortesía
        if ($request->filled('courtesy_code')) {
            $courtesyCode = Coupon::where('numeric_code', $request->courtesy_code)
                ->where('is_consumed', false)
                ->where('event_id', $eventAssistant->event_id)
                ->first();

            if (!$courtesyCode) {
                return redirect()->back()->withErrors(['error' => 'El código de cortesía no es válido o ya ha sido utilizado.']);
            }

            // Marcar cupón como usado
            $courtesyCode->is_consumed = true;
            $courtesyCode->eventAssistant_id = $eventAssistant->id;
            $courtesyCode->save();

            $eventAssistant->is_paid = true;
            $eventAssistant->save();

            // Generar PDF y mostrar vista de confirmación
            $PDFController = new PDFController();
            $meta = $PDFController->buildPDF_Mail($request->event_assistant_id);
            return view('email.return_email_ticketevent', compact('meta'));
        }

        // Guardar comprobante si existe
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Si seleccionó PayPal, redirige a checkout
        if ($request->payment_method == 'PayPal') {
            $event = Event::find($eventAssistant->event_id);
            $pago = [
                'event_assistant_id' => $request->event_assistant_id,
                'payer_name' => $request->payer_name,
                'payer_document_number' => $request->payer_document_number,
                'amount' => round($request->amount * 0.000234),
                'evento' => $event->name,
            ];
            return view('paypal.checkout', compact('pago'));
        }

        // Crear registro de pago
        Payment::create([
            'event_assistant_id' => $request->event_assistant_id,
            'payer_name' => $request->payer_name,
            'payer_document_type' => $request->payer_document_type,
            'payer_document_number' => $request->payer_document_number,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_proof' => $paymentProofPath,
            'description' => 'Pago de Ticket',
        ]);

        // Marcar como pagado
        $eventAssistant->is_paid = true;
        $eventAssistant->save();

        // Redirigir con mensaje de éxito
        return redirect()->back()->with('success', 'Se ha registrado el pago correctamente');
    }

    public function sendEmailInfoPago($id)
    {
        // Buscar el registro de EventAssistant por ID
        $eventAssistant = EventAssistant::find($id);

        // Asegúrate de que el asistente existe
        if (!$eventAssistant || !$eventAssistant->user) {
            return response()->json(['message' => 'No se ha encontrado el usuario respectivo'], 404);
        }

        // Obtener el correo electrónico del usuario relacionado
        $email = $eventAssistant->user->email;
        // Asegúrate de que el email existe
        if (!$email) {
            return response()->json(['message' => 'No se ha encontrado el email respectivo'], 404);
        }

        // Enviar el correo
        Mail::send(
            'emails.infoPagoAssistant',
            ['eventAssistant' => $eventAssistant],
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Información del evento');
            }
        );

        return response()->json(['message' => 'Email enviado a ' . $email]);
    }



    public function showMassPayload($idEvent)
    {
        $event = Event::find($idEvent);
        return view('eventAssistant.massPayload', compact('event'));
    }

    public function courtesyCode($idEvent)
    {
        $event = Event::find($idEvent);
        return view('eventAssistant.courtesyCode', compact('event'));
    }
    public function generateCoupons(Request $request)
    {
        $eventId = $request->event_id; // Asegúrate de pasar el ID del evento
        $numberOfCoupons = $request->number_of_coupons; // Número de cupones a generar

        for ($i = 0; $i < $numberOfCoupons; $i++) {
            Coupon::create([
                'event_id' => $eventId,
                'is_consumed' => false,
                'ticket_type_id' => $request->ticket_type_id,
            ]);
        }

        return response()->json(['message' => 'Cupones generados correctamente']);
    }
    public function getCoupons($eventId)
    {
        $coupons = Coupon::where('event_id', $eventId)->with('ticketType')->get();
        return response()->json($coupons);
    }
}
