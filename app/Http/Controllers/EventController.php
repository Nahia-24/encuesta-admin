<?php

namespace App\Http\Controllers;

use App\Models\AdditionalParameter;
use App\Models\Coupon;
use App\Models\Departament;
use App\Models\Event;
use App\Models\EventAssistant;
use App\Models\TicketFeatures;
use App\Models\TicketType;
use App\Models\TicketCharacteristic;
use App\Models\User;
use App\Models\UserEventParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EventController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        // $status = config('statusEvento.'.$search);
        // return $status;
        $eventos = Event::query()
            ->when($search, function ($query, $search) {
                $status = config('statusEvento.' . $search);
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                if ($status) {
                    $query
                        ->orWhere('status', 'like', "%{$status}%");
                }
            })
            ->paginate(10);
        return view('event.index', compact('eventos', 'search'));
    }

    public function create()
    {
        $departments = Departament::all();
        $features = TicketFeatures::all();
        $characteristics = TicketCharacteristic::all();

        return view('event.create', compact('departments', 'features', 'characteristics'));
    }

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|max:255',
            'status' => 'required',
            'description' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'city_id' => 'required|integer|exists:cities,id',
            'event_date' => 'required|date',
            'event_date_end' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'header_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color_one' => 'nullable|string|max:7',
            'color_two' => 'nullable|string|max:7',

            // Tipos de entradas
            'ticketTypes.*.name' => 'required|string|max:255',
            'ticketTypes.*.capacity' => 'required|integer|min:1',
            'ticketTypes.*.price' => 'required|numeric',
            'ticketTypes.*.characteristics' => 'required|array|min:1',
            'ticketTypes.*.characteristics.*' => 'exists:ticket_characteristics,id',

            // Campos adicionales
            'additionalFields.*.label' => 'required|string|max:255',
            'additionalFields.*.value' => 'required|string|max:255',
        ]);

        // Manejar la carga de la imagen
        $imagePath = null;
        if ($request->hasFile('header_image_path')) {
            $image = $request->file('header_image_path');
            $imagePath = $image->store('event_images', 'public');
        }

        // Crear el evento
        $event = new Event();
        $event->name = $request->name;
        $event->description = $request->description;
        $event->capacity = $request->capacity;
        $event->city_id = $request->city_id;
        $event->event_date = $request->event_date;
        $event->event_date_end = $request->event_date_end;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        $event->address = $request->address;
        $event->header_image_path = $imagePath;
        $event->status = $request->status;
        $event->color_one = $request->color_one;
        $event->color_two = $request->color_two;
        // Convertir los campos adicionales a JSON
        if ($request->input('additionalFields')) {
            $event->additionalFields = json_encode($request->input('additionalFields', []));
        }

        // Guardar el ID del usuario que creó el evento
        $event->created_by = Auth::user()->id;
        $event->save();

        // Crear los tipos de entradas
        if ($request->ticketTypes) {
            foreach ($request->ticketTypes as $ticketTypeData) {
                $ticketType = $event->ticketTypes()->create([
                    'name' => $ticketTypeData['name'],
                    'capacity' => $ticketTypeData['capacity'],
                    'price' => $ticketTypeData['price'],
                ]);

                // Asignar características
                $ticketType->features()->sync($ticketTypeData['features']);
            }
        }

        return redirect()->route('event.index')->with('success', 'Evento creado exitosamente.');
    }

    public function edit($id)
    {
        $event = Event::with('ticketTypes.characteristics')->findOrFail($id);
        $departments = Departament::all();
        $features = TicketFeatures::all();
        $characteristics = TicketCharacteristic::all(); // ✅ Agregado

        return view('event.update', compact('event', 'departments', 'features', 'characteristics'));
    }

    public function update(Request $request)
    {
        try {
            $id = $request->id;
            $event = Event::findOrFail($id);

            // Validación de campos
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'capacity' => 'required|integer|min:1',
                'event_date' => 'required|date',
                'event_date_end' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'address' => 'required|max:255',
                'status' => 'required',
                'color_one' => 'nullable|string|max:7',
                'color_two' => 'nullable|string|max:7',
                'header_image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'ticketTypes.*.name' => 'required|string|max:255',
                'ticketTypes.*.capacity' => 'required|integer|min:1',
                'ticketTypes.*.price' => 'required|numeric',
                'ticketTypes.*.characteristics' => 'required|array|min:1',
                'ticketTypes.*.characteristics.*' => 'exists:ticket_characteristics,id',
            ]);

            // Imagen nueva (si aplica)
            if ($request->hasFile('header_image_path')) {
                if ($event->header_image_path) {
                    Storage::disk('public')->delete($event->header_image_path);
                }
                $event->header_image_path = $request->file('header_image_path')->store('event_images', 'public');
            }

            // Actualizar campos básicos
            $event->fill([
                'name' => $request->name,
                'description' => $request->description,
                'capacity' => $request->capacity,
                'event_date' => $request->event_date,
                'event_date_end' => $request->event_date_end,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'address' => $request->address,
                'status' => $request->status,
                'color_one' => $request->color_one,
                'color_two' => $request->color_two,
                'city_id' => $request->city_id,
            ]);

            if ($request->filled('additionalFields')) {
                $event->additionalFields = json_encode($request->input('additionalFields', []));
            }

            $event->save();

            // Manejo de tipos de ticket
            $newTicketTypeIds = collect($request->ticketTypes)->pluck('id')->filter()->all();

            // Eliminar los ticketTypes que ya no están
            $event->ticketTypes()->whereNotIn('id', $newTicketTypeIds)->get()->each(function ($ticketType) {
                if ($ticketType->EventAssistant()->exists()) {
                    throw new \Exception("El tipo de boleta '{$ticketType->name}' no puede ser eliminado porque está asociado a asistentes.");
                }
                $ticketType->delete();
            });

            // Crear/actualizar tickets
            foreach ($request->ticketTypes as $ticketTypeData) {
                $ticketType = TicketType::updateOrCreate(
                    ['id' => $ticketTypeData['id'] ?? null, 'event_id' => $event->id],
                    [
                        'name' => $ticketTypeData['name'],
                        'capacity' => $ticketTypeData['capacity'],
                        'price' => $ticketTypeData['price'],
                    ]
                );

                // Sincronizar características
                $ticketType->characteristics()->sync($ticketTypeData['characteristics'] ?? []);
            }

            return redirect()->route('event.index')->with('success', 'Evento actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('event.edit', $request->id)->with('error', $e->getMessage());
        }
    }

    public function generatePublicLink($id)
    {
        $event = Event::findOrFail($id);

        // Generar GUID único
        $guid = (string) Str::uuid();

        // Guardar el GUID en el evento
        $event->public_link = $guid;
        $event->save();

        // Devolver el enlace completo
        return redirect()->route('event.index')->with('success', 'Enlace público generado: ' . route('event.register', $guid));
    }

    public function showPublicRegistrationForm($public_link)
    {
        // Busca el evento por el enlace público
        $event = Event::where('public_link', $public_link)->firstOrFail();
        $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        $departments = Departament::all();
        $ticketTypes  = TicketType::where('event_id', $event->id)->get();

        // Retorna la vista de registro, pasando el evento
        return view('event.public_registration', compact('event', 'departments', 'additionalParameters', 'ticketTypes'));
    }

    public function submitPublicRegistration(Request $request, $public_link)
    {
        $event = Event::where('public_link', $public_link)->firstOrFail();

        // Verificar si se proporcionó un código de cortesía
        if ($request->courtesy_code) {
            $coupon = Coupon::where('numeric_code', $request->courtesy_code)
                ->where('event_id', $event->id)
                ->where('is_consumed', false)
                ->with('ticketType') // Asegura la relación con ticketType
                ->first();
            if (!$coupon) {
                return redirect()->back()->with('error', 'Inscripción NO exitosa. CUPON INVALIDO');
            }
        }

        // Verificar si el evento ya ha finalizado
        $eventAssistantController = new EventAssistantController();
        if ($eventAssistantController->eventoFinalizado($event->id)) {
            return redirect()->back()->with('error', 'No se puede realizar esta acción porque el evento ya ha sido finalizado.');
        }

        // Construir reglas de validación dinámicas basadas en los parámetros de registro del evento
        $registrationParameters = json_decode($event->registration_parameters, true) ?? [];
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
                case 'city_id':
                    $validationRules[$param] = 'nullable|exists:cities,id';
                    break;
                case 'birth_date':
                    $validationRules[$param] = 'nullable|date';
                    break;
            }
        }

        // Validar el request
        $validatedData = $request->validate($validationRules);
        $user = null;
        if ($request->has('email')) {
            // Verificar si el usuario ya existe por correo
            $user = User::where('email', $request->email)
                ->first();
        } elseif ($request->has('document_number')) {
            // Verificar si el usuario ya existe por número de documento
            $user = User::where('document_number', $request->document_number)
                ->first();
        }
        if ($user) {
            // Si el usuario existe, actualizar su información
            $user->update($validatedData);
        } else {
            // Si no existe, crearlo
            $user = User::create(array_merge($validatedData, ['status' => false]));
        }
        if (!$user->hasRole('assistant')) {
            $assistantRole = Role::firstOrCreate(['name' => 'assistant']); // Crear el rol si no existe
            $user->assignRole($assistantRole);
        }

        // Verificar si el usuario ya está inscrito en el evento
        $eventAssistant = EventAssistant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($eventAssistant) {
            // Si ya está inscrito, mostrar mensaje de error
            return redirect()->back()->with('error', 'El usuario ya está inscrito en este evento.');
        } else {
            // Si no está inscrito, crear el registro en `event_assistant`
            $guardianId = $request->input('guardian_id') ?? null;
            $eventAssistant = EventAssistant::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'ticket_type_id' => $request['id_ticket'] ?? null,
                'has_entered' => false,
                'guardian_id' => $guardianId,
            ]);

            // Si hay un código de cortesía, marcarlo como consumido y asignarlo al asistente
            if (isset($coupon)) {
                $coupon->is_consumed = true;
                $coupon->event_assistant_id = $eventAssistant->id;
                $coupon->save();
                $eventAssistant->is_paid = true;
                $eventAssistant->ticket_type_id = $coupon->ticket_type_id;
                $eventAssistant->save();
            }
        }

        // Obtener los parámetros adicionales definidos para el evento
        $definedParameters = AdditionalParameter::where('event_id', $event->id)->get();
        $userFillableColumns = (new User())->getFillable();

        // Detectar y almacenar parámetros adicionales enviados en el registro
        $additionalParameters = $request->except(array_merge(['_token'], $userFillableColumns)); // Excluir columnas del modelo User
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

        // Generar el código QR y devolver la vista de registro exitoso
        $qrcode = $eventAssistant->qrCode;
        $message = 'Inscripción exitosa.';
        return view('event.public_registrated', compact('event', 'qrcode', 'message'));
    }

    public function setRegistrationParameters($id)
    {
        $event = Event::findOrFail($id);
        $additional_parameters = AdditionalParameter::where('event_id', $id)->get();
        return view('event.set-registration-parameters', compact('event', 'additional_parameters'));
    }

    public function storeRegistrationParameters(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        // Validar la entrada de los campos seleccionados
        $request->validate([
            'fields' => 'array',
            'fields.*' => 'in:name,lastname,email,type_document,document_number,phone,status,profile_photo_path,city_id,birth_date',
        ]);

        // Almacenar los campos seleccionados como parámetros de inscripción
        $parameters = json_encode($request->fields); // Convertir a JSON

        $event->registration_parameters = $parameters;
        $event->save();

        // Manejar los parámetros adicionales
        $additionalParameters = $request->input('additional_parameters', []);

        // Obtener los nombres de los parámetros adicionales enviados desde el formulario
        $newParameterNames = array_column($additionalParameters, 'name');

        // Obtener todos los parámetros adicionales actuales en la base de datos para este evento
        $existingParameters = AdditionalParameter::where('event_id', $event->id)->get();

        // Eliminar los parámetros adicionales que ya no están presentes en los nuevos datos enviados
        foreach ($existingParameters as $existingParameter) {
            if (!in_array($existingParameter->name, $newParameterNames)) {
                $existingParameter->delete();
            }
        }

        // Agregar o actualizar los parámetros adicionales
        foreach ($additionalParameters as $param) {
            if (!empty($param['name']) && !empty($param['type'])) {
                // Verificar si ya existe un parámetro adicional con el mismo 'name' y 'event_id'
                $existingParameter = AdditionalParameter::where('event_id', $event->id)
                    ->where('name', $param['name'])
                    ->first();

                if ($existingParameter) {
                    $existingParameter->update([
                        'type' => $param['type']
                    ]);
                } else {
                    AdditionalParameter::create([
                        'event_id' => $event->id,
                        'name' => $param['name'],
                        'type' => $param['type']
                    ]);
                }
            }
        }
        return redirect()->route('event.index')->with('success', 'Parámetros de inscripción guardados correctamente.');
    }

    /**
     * Eliminar un evento junto con sus relaciones (tickets, asistentes, etc.)
     */
    public function destroy($id)
    {
        // Encontrar el evento o fallar
        $event = Event::findOrFail($id);

        // (Opcional) Verificar permisos o si hay restricciones
        // e.g. si ya hay asistentes, podrías impedir borrarlo:
        if ($event->assistants()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar un evento con asistentes registrados.');
        }

        // Eliminar imagen de portada si existe
        if ($event->header_image_path) {
            \Storage::disk('public')->delete($event->header_image_path);
        }

        // Eliminar el evento (se encarga de cascada en migraciones)
        $event->delete();

        return redirect()
            ->route('event.index')
            ->with('success', 'Evento eliminado exitosamente.');
    }
}
