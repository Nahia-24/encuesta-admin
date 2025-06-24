@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Pagar Ticket</title>
    <link rel="stylesheet" href="{{ url('css/blade.css') }}">
@endsection

@section('subcontent')
    @if (session('error'))
        <div class="intro-x mt-4 alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mt-10 ">
        <h2 class="text-lg font-medium">Informacion del Pago</h2>
        <a href="{{ route('eventAssistant.sendEmailInfoPago', ['id' => $eventAssistant->id]) }}" target="_blank"
            class="flex items-center gap-2 text-info bg-white border border-info px-4 py-2 rounded-md hover:bg-info/10 transition duration-150">
            <x-base.lucide icon="send" class="w-5 h-5" />
            <span>Enviar Correo</span>
        </a>

        <x-base.button class="shadow-md h-9 px-8 text-sm" type="button" variant="primary"
            onclick="window.location='{{ route('eventAssistant.index', ['idEvent' => $eventAssistant->event_id]) }}'">
            Volver
        </x-base.button>
    </div>

    @php
        // Obtener los parámetros guardados en registration_parameters
        $selectedFields = json_decode($eventAssistant->event->registration_parameters, true) ?? [];
        $additionalParameters = json_decode($eventAssistant->event->additionalParameters, true) ?? [];
    @endphp
    <div class="box p-5 space-y-6 mt-10">
        <div>
            <h3 class="text-lg font-semibold border-b pb-2 mb-4">Información del Asistente</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($selectedFields as $field)
                    <div>
                        <span class="font-medium text-gray-600">
                            {{ config("traductorColumnasUsers.$field", ucfirst(str_replace('_', ' ', $field))) }}:
                        </span>
                        <div class="text-black">{{ $eventAssistant->user->$field }}</div>
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
                        <span
                            class="font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}:</span>
                        <div class="text-black">{{ $userParameter ? $userParameter->value : '-' }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold mt-8 mb-2 border-b border-gray-300 pb-2">Información del Evento</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><strong>Nombre:</strong> {{ $eventAssistant->event->name }}</div>
                <div><strong>Descripción:</strong> {{ $eventAssistant->event->description }}</div>
                <div><strong>Fecha:</strong> {{ $eventAssistant->event->event_date }}</div>
                <div><strong>Hora Inicio:</strong> {{ $eventAssistant->event->start_time }}</div>
                <div><strong>Hora Fin:</strong> {{ $eventAssistant->event->end_time }}</div>
                <div><strong>Ciudad:</strong> {{ $eventAssistant->event->city->name ?? 'N/A' }}</div>
                <div><strong>Capacidad:</strong> {{ $eventAssistant->event->capacity }}</div>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold mt-8 mb-2 border-b border-gray-300 pb-2">Características del Ticket</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <strong>Tipo de Entrada:</strong>
                    {{ $eventAssistant->ticketType?->name ?? 'SIN REGISTRO' }}
                </div>
                <div>
                    <strong>Precio:</strong>
                    ${{ $eventAssistant->ticketType?->formattedPrice() }}
                </div>
                <div class="col-span-2">
                    <strong>Características:</strong>
                    @php
                        $characteristics = $eventAssistant->ticketType?->characteristics;
                    @endphp
                    <div>
                        @if ($characteristics && $characteristics->count())
                            {{ $characteristics->pluck('name')->implode(', ') }}
                        @else
                            <em>No hay características</em>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LÓGICA PRINCIPAL CORREGIDA --}}
    @if (!$eventAssistant->is_paid)
        {{-- CASO 1: AÚN NO ESTÁ PAGADO --}}

        {{-- Mostramos abonos si existen (una sola vez) --}}
        @if ($eventAssistant->totalPayments() > 0)
            <div class="mt-5">
                <div class="box p-5">
                    Actualmente se tiene registrado un abono Total de {{ $eventAssistant->totalPayments() }}
                    @foreach ($eventAssistant->payments as $payment)
                        <div class="mb-4 box p-1">
                            Pago por un valor de <strong>${{ number_format($payment->amount, 0, ',', '.') }}</strong>
                            por <strong>{{ $payment->payer_name }}</strong>
                            <a class="ml-1 underline" target="_blank"
                                href="{{ route('payments.generatePDF', ['id' => $payment->id]) }}">Generar PDF</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Mostramos el formulario de pago --}}
        <div class="mt-5">
            <div class="bg-white p-8 shadow-lg rounded-lg w-full max-w-2xl mx-auto">
                <h3 class="text-2xl font-bold mb-6 text-gray-800 text-center">Realizar Pago</h3>

                <form action="{{ route('eventAssistant.payment.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf
                    <input type="hidden" name="event_assistant_id" value="{{ $eventAssistant->id }}">

                    <div>
                        <label for="payer_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del
                            Pagador</label>
                        <input type="text" id="payer_name" name="payer_name"
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label for="payer_document_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de
                            Documento</label>
                        <select id="payer_document_type" name="payer_document_type"
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            required>
                            <option value="" disabled selected>Seleccione una opción</option>
                            <option value="CC">Cédula de Ciudadanía</option>
                            <option value="TI">Tarjeta de Identidad</option>
                            <option value="PP">Pasaporte</option>
                        </select>
                    </div>

                    <div>
                        <label for="payer_document_number" class="block text-sm font-medium text-gray-700 mb-1">No. de
                            Documento</label>
                        <input type="text" id="payer_document_number" name="payer_document_number"
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center cursor-pointer">
                            <!-- Checkbox oculto -->
                            <input type="checkbox" id="has_coupon" name="has_coupon" onchange="toggleCouponField()"
                                style="display: none;">
                            <!-- Título visible arriba del toggle -->
                            <label for="has_coupon" class="block text-sm font-medium text-gray-700 mb-2">
                                ¿Tienes un cupón de cortesía?
                            </label>
                            <!-- Switch visual -->
                            <div class="relative w-12 h-6 bg-gray-300 rounded-full transition-colors duration-300 mr-3"
                                id="switch-bg">
                                <div id="switch-thumb"
                                    class="absolute left-[2px] w-5 h-5 bg-white rounded-full shadow transition-transform duration-300"
                                    style="top: 0.5px;">
                                </div>
                            </div>
                            <!-- Texto -->
                            <span class="text-sm text-gray-700">Activar</span>
                        </label>
                        <!-- Campo del cupón -->
                        <div class="mt-4 space-y-2 hidden" id="coupon_field">
                            <label for="courtesy_code" class="block text-sm font-medium text-gray-700">
                                Cupón de Cortesía
                            </label>
                            <div class="flex gap-2">
                                <input id="courtesy_code" name="courtesy_code" type="text"
                                    class="flex-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Ingresa tu cupón" />
                                <button type="button" class="bg-[#1F3262] text-white text-sm px-4 py-2 rounded">
                                    Validar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Campo de monto a pagar --}}
                    <div class="mt-3">
                        <div id="amountDiv">
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Cantidad a
                                Pagar</label>
                            <input type="number" id="amount" name="amount"
                                class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm bg-gray-100"
                                value="{{ $eventAssistant->ticketType->price ?? 0 }}" readonly>
                        </div>

                        <div id="paymentMethodDiv">
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Forma de
                                Pago</label>
                            <select id="payment_method" name="payment_method"
                                class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                onchange="togglePaymentProof()" required>
                                <option value="" disabled selected>Seleccione una opción</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="PayPal">PayPal</option>
                            </select>
                        </div>

                        <div id="transfer_proof" class="hidden">
                            <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-1">Comprobante de
                                Transferencia</label>
                            <input type="file" id="payment_proof" name="payment_proof"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-center">
                        <x-base.button type="submit" variant="primary" class="w-full md:w-auto py-3 px-6">
                            Realizar Pago
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    @else
        {{-- CASO 2: EL TICKET YA ESTÁ PAGADO --}}
        <div class="mt-5">
            <div class="box p-5">
                <p>Actualmente la Boleta ya está registrada como pagada.</p>

                {{-- Historial de pagos si la boleta ya fue pagada --}}
                @if ($eventAssistant->payments->isNotEmpty())
                    <h4 class="font-medium mt-4">Historial de Pagos</h4>
                    @foreach ($eventAssistant->payments as $payment)
                        <div class="border-t mt-4 pt-4">
                            Pago por un valor de
                            <strong>${{ number_format($payment->amount, 0, ',', '.') }}</strong>
                            <a class="ml-2 underline" target="_blank"
                                href="{{ route('payments.generatePDF', ['id' => $payment->id]) }}">Generar PDF</a>
                            @if ($payment->payment_proof)
                                <img class="mt-2 rounded-md max-w-sm"
                                    src="{{ asset('storage/' . $payment->payment_proof) }}" alt="Comprobante pago">
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif

@endsection

{{-- El SCRIPT se mueve al final, pero antes de @endpush o al final de la sección --}}
@push('scripts')
    <script>
        function toggleCouponField() {
            const checkbox = document.getElementById('has_coupon');
            const couponField = document.getElementById('coupon_field');
            const switchBg = document.getElementById('switch-bg');
            const switchThumb = document.getElementById('switch-thumb');
            if (checkbox.checked) {
                couponField.classList.remove('hidden');
                switchBg.style.backgroundColor = '#1F3262'; // Azul oscuro
                switchThumb.style.transform = 'translateX(24px)';
            } else {
                couponField.classList.add('hidden');
                switchBg.style.backgroundColor = '#D1D5DB'; // Gris claro
                switchThumb.style.transform = 'translateX(0)';
            }
        }
        // Estado inicial
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('switch-bg').style.backgroundColor = '#D1D5DB';
        });

        function togglePaymentProof() {
            const paymentMethod = document.getElementById('payment_method').value;
            const proofDiv = document.getElementById('transfer_proof');
            if (paymentMethod === 'transferencia') {
                proofDiv.classList.remove('hidden');
            } else {
                proofDiv.classList.add('hidden');
            }
        }
    </script>
@endpush
