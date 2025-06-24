@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Detalles del Asistente y del Evento</title>
@endsection

@section('subcontent')
    <div class="box p-5 mt-5">
        <a href="{{ route('eventAssistant.index', ['idEvent' => $eventAssistant->event_id]) }}"
            class="text-blue-600 hover:underline">
            ← Volver al listado de asistentes
        </a>
    </div>

    <h2 class="intro-y mt-10 text-lg font-medium">Detalles del Asistente y del Evento</h2>

    @if (session('success'))
        <x-base.alert variant="success" class="mb-2 flex items-center">
            <x-base.lucide class="mr-2 h-6 w-6" icon="CheckCircle" />
            {{ session('success') }}
        </x-base.alert>
    @endif

    @if (session('error'))
        <x-base.alert variant="danger" class="mb-2 flex items-center">
            <x-base.lucide class="mr-2 h-6 w-6" icon="AlertCircle" />
            {{ session('error') }}
        </x-base.alert>
    @endif

    @if (session('warning'))
        <x-base.alert variant="warning" class="mb-2 flex items-center">
            <x-base.lucide class="mr-2 h-6 w-6" icon="AlertCircle" />
            {{ session('warning') }}
        </x-base.alert>
    @endif

    {{-- ESTADO DE PAGO --}}
    <div class="mt-5">
        @if ($eventAssistant->is_paid)
            <div class="alert bg-success text-white box p-5 rounded-md">
                <h3 class="text-lg font-semibold">Estado del Pago del Ticket:</h3>
                <p>Pagado</p>
            </div>
        @elseif ($eventAssistant->totalPayments() == 0)
            <div class="alert bg-danger text-white box p-5 rounded-md">
                <h3 class="text-lg font-semibold">Estado del Pago del Ticket:</h3>
                <p>No Pagado</p>
            </div>
        @else
            <div class="alert bg-warning text-black box p-5 rounded-md">
                <h3 class="text-lg font-semibold">Estado del Pago del Ticket:</h3>
                <p>Pendiente</p>
            </div>
        @endif
    </div>

    {{-- INFORMACIÓN PRINCIPAL --}}
    <div class="box p-5 mt-5 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- INFORMACIÓN DEL ASISTENTE --}}
            <div>
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Información del Asistente</h3>
                @php
                    $selectedFields = json_decode($eventAssistant->event->registration_parameters, true) ?? [];
                    $additionalParameters = json_decode($eventAssistant->event->additionalParameters, true) ?? [];
                @endphp

                <div class="space-y-2 text-sm text-gray-700">
                    @foreach ($selectedFields as $field)
                        <div>
                            <strong>{{ config("traductorColumnasUsers.$field", ucfirst(str_replace('_', ' ', $field))) }}:</strong>
                            <span class="text-black">{{ $eventAssistant->user->$field }}</span>
                        </div>
                    @endforeach

                    @foreach ($additionalParameters as $parameter)
                        @php
                            $userParameter = $eventAssistant->eventParameters
                                ->where('event_id', $eventAssistant->event_id)
                                ->where('additional_parameter_id', $parameter['id'])
                                ->first();
                        @endphp
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}:</strong>
                            <span class="text-black">{{ $userParameter ? $userParameter->value : '-' }}</span>
                        </div>
                    @endforeach

                    <div><strong>Tipo de Ticket:</strong> {{ $eventAssistant->ticketType->name ?? 'N/A' }}</div>
                    <div><strong>Fecha de Registro:</strong> {{ $eventAssistant->created_at->format('d/m/Y') }}</div>
                    <div><strong>GUID:</strong> {{ $eventAssistant->guid }}</div>
                </div>
            </div>

            {{-- INFORMACIÓN DEL EVENTO --}}
            <div>
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Información del Evento</h3>
                <div class="space-y-2 text-sm text-gray-700">
                    <div><strong>Nombre:</strong> {{ $eventAssistant->event->name }}</div>
                    <div><strong>Descripción:</strong> {{ $eventAssistant->event->description }}</div>
                    <div><strong>Fecha:</strong> {{ $eventAssistant->event->event_date }}</div>
                    <div><strong>Hora Inicio:</strong> {{ $eventAssistant->event->start_time }}</div>
                    <div><strong>Hora Fin:</strong> {{ $eventAssistant->event->end_time }}</div>
                    <div><strong>Ciudad:</strong> {{ $eventAssistant->event->city->name ?? 'N/A' }}</div>
                    <div><strong>Capacidad:</strong> {{ $eventAssistant->event->capacity }}</div>
                </div>
            </div>
        </div>

        {{-- CÓDIGO QR --}}
        <div>
            <h3 class="text-lg font-semibold mt-6 mb-2 border-b pb-2">Código QR</h3>
            <div class="mt-2">
                {!! $eventAssistant->qrCode !!}
            </div>
        </div>

        {{-- CARACTERÍSTICAS DEL TICKET --}}
        <div>
            <h3 class="text-lg font-semibold mt-6 mb-2 border-b pb-2">Características del Ticket</h3>
            @php
                $characteristics = $eventAssistant?->ticketType?->characteristics ?? [];
            @endphp
            <ul class="list-disc ml-5 text-sm text-gray-700">
                @forelse ($characteristics as $item)
                    <li>
                        <strong>{{ $item }}</strong> <span class="text-gray-500">No Consumible</span>
                    </li>
                @empty
                    <li>No hay características disponibles para este ticket.</li>
                @endforelse
            </ul>
        </div>

        {{-- BOTONES DE ACCIÓN --}}
        <div class="mt-6">
            @if (!$eventAssistant->has_entered && !$eventAssistant->rejected)
                <form action="{{ route('eventAssistant.registerEntry', $eventAssistant->id) }}" method="POST" class="inline-block mr-2">
                    @csrf
                    @method('PATCH')
                    <x-base.button type="submit" variant="primary">
                        Registrar Ingreso
                    </x-base.button>
                </form>

                <form action="{{ route('eventAssistant.rejectEntry', $eventAssistant->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('PATCH')
                    <x-base.button type="submit" variant="danger">
                        Rechazar Ingreso
                    </x-base.button>
                </form>
            @else
                @if ($eventAssistant->has_entered)
                    <x-base.alert variant="success" class="mt-2">
                        Ya ha registrado el ingreso el {{ $eventAssistant->entry_time }}
                    </x-base.alert>
                @endif

                @if ($eventAssistant->rejected)
                    <x-base.alert variant="danger" class="mt-2">
                        Ingreso rechazado el {{ $eventAssistant->rejected_time }}
                    </x-base.alert>

                    @if (!$eventAssistant->has_entered)
                        <form action="{{ route('eventAssistant.registerEntry', $eventAssistant->id) }}" method="POST" class="mt-2">
                            @csrf
                            @method('PATCH')
                            <x-base.button type="submit" variant="primary">
                                Registrar Ingreso
                            </x-base.button>
                        </form>
                    @endif
                @endif
            @endif
        </div>
    </div>
@endsection
