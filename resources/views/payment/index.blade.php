@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Asistentes del Evento</title>
@endsection

@section('subcontent')
    @if (session('error'))
        <x-base.alert class="mb-2 flex items-center" variant="danger">
            <x-base.lucide class="mr-2 h-6 w-6" icon="AlertCircle" />
            {{ session('error') }}
        </x-base.alert>
    @endif
    
    <div class="flex justify-between items-center mt-10">
        <h2 class="text-lg font-medium">Recaudos</h2>
        <a href="{{ route('event.index') }}">
            <x-base.button class="shadow-md h-9 px-8 text-sm" variant="primary">
                Volver a Eventos
            </x-base.button>
        </a>
    </div>

        <!-- BEGIN: DashBoard -->
        <div class="mt-5">
            <div class="box p-5">
                <h3 class="text-lg font-medium">Información del Pagos y cupones</h3>
                <p><strong>recaudoTotal:</strong> {{ number_format($recaudoTotal, 0, '', '.') }} </p>
                <p><strong>asistentes que han pagado:</strong> {{ $asistentesPagos }} </p>
                <p><strong>asistentes sin entrada:</strong> {{ $asistentesSinEntrada }} </p>
                <p><strong>cupones redimidos:</strong> {{ $cuponesRedimidos }} </p>
                <p><strong>cupones No redimidos:</strong> {{ $cuponesNoRedimidos }} </p>
                <br>
            </div>
        </div>
        <!-- END: Data List -->
        <!-- BEGIN: Botones Generales -->
        <div class="intro-y col-span-12 mt-2 flex flex-wrap items-center sm:flex-nowrap">
            <x-base.menu>
                <x-base.menu.button class="!box" as="x-base.button">
                    <span class="flex items-center justify-center">
                        <x-base.lucide class="h-4 w-4" icon="Plus" />
                        Reporte Pagos
                    </span>
                </x-base.menu.button>
                <x-base.menu.items class="w-40">
                    <x-base.menu.item>
                        <a class="mr-3" target="_blank"
                            href="{{ route('payment.exportExcel', [
                                'idEvent' => $idEvent,
                                'search' => request()->input('search'),
                                'additionalParameters' => request()->input('additionalParameters', []),
                            ]) }}">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Pagos Realizados (EXCEL
                        </a>
                        <a class="mr-3" target="_blank"
                            href="{{ route('paymentStatus.exportExcel', [
                                'idEvent' => $idEvent,
                                'search' => request()->input('search'),
                                'additionalParameters' => request()->input('additionalParameters', []),
                            ]) }}">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Status Pago (EXCEL)
                        </a>

                    </x-base.menu.item>
                </x-base.menu.items>
            </x-base.menu>

            <div class="mt-3 w-full sm:ml-auto sm:mt-0 sm:w-auto md:ml-0">
                <form method="GET" action="{{ route('payments.index', ['idEvent' => $idEvent]) }}">
                    <div class="relative w-56 text-slate-500">
                        <input type="text" name="search" class="!box w-56 pr-10"
                            value="{{ request()->input('search') }}" placeholder="Buscar..." />

                        <button type="submit" class="absolute inset-y-0 right-0 my-auto mr-3 h-4 w-4">
                            <x-base.lucide icon="Search" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- BEGIN: Payment Table -->
        @php
            $selectedFields = json_decode($event->registration_parameters, true) ?? [];
        @endphp
        <div class="intro-y col-span-12 bg-white dark:bg-darkmode-700 rounded-lg shadow-lg p-5 mt-5">
            <div class="overflow-x-auto">
                <x-base.table class="-mt-2 border-separate border-spacing-y-2 min-w-full">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <!-- Columnas dinámicas -->
                            @foreach (['name', 'email', 'document_number'] as $field)
                                @if (in_array($field, $selectedFields))
                                    <x-base.table.th
                                        class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold">
                                        {{ config("traductorColumnasUsers.$field", ucfirst(str_replace('_', ' ', $field))) }}
                                    </x-base.table.th>
                                @endif
                            @endforeach

                            <!-- Columnas fijas para pagos -->
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold">
                                Tipo de Boleta
                            </x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold">
                                Estatus de Pago
                            </x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold">
                                Monto Pagado
                            </x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold">
                                Entrada
                            </x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold">
                                Acciones
                            </x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($asistentes as $asistente)
                            <x-base.table.tr
                                class="intro-x bg-white dark:bg-darkmode-600 shadow-md rounded-lg hover:shadow-xl transition-shadow duration-200">
                                <!-- Datos dinámicos -->
                                @foreach (['name', 'email', 'document_number'] as $field)
                                    @if (in_array($field, $selectedFields))
                                        <x-base.table.td class="text-center align-middle p-4 border-0">
                                            {{ $asistente->user->$field }}
                                        </x-base.table.td>
                                    @endif
                                @endforeach

                                <!-- Información de pago -->
                                <x-base.table.td class="text-center align-middle p-4 border-0">
                                    {{ $asistente->ticketType?->name ?? 'SIN REGISTRO' }}
                                </x-base.table.td>

                                <x-base.table.td class="text-center align-middle p-4 border-0">
                                    @if ($asistente->is_paid)
                                        @php
                                            $payments = \App\Models\Payment::where(
                                                'event_assistant_id',
                                                $asistente->id,
                                            )->get();
                                            $coupon = $payments->isEmpty()
                                                ? \App\Models\Coupon::where('event_assistant_id', $asistente->id)
                                                    ->where('is_consumed', true)
                                                    ->first()
                                                : null;
                                        @endphp

                                        @if ($payments->isNotEmpty())
                                            <div class="alert bg-success text-slate-900 p-2 rounded-md">
                                                Pagado
                                            </div>
                                        @elseif($coupon)
                                            <div class="alert bg-success text-slate-900 p-2 rounded-md">
                                                Cupón: {{ $coupon->numeric_code }}
                                            </div>
                                        @else
                                            <div class="alert bg-success text-slate-900 p-2 rounded-md">
                                                Pagado
                                            </div>
                                        @endif
                                    @else
                                        <div
                                            class="alert bg-{{ $asistente->totalPayments() > 0 ? 'warning' : 'danger' }} text-slate-900 p-2 rounded-md">
                                            {{ $asistente->totalPayments() > 0 ? 'Pendiente' : 'No Pagado' }}
                                        </div>
                                    @endif
                                </x-base.table.td>

                                <x-base.table.td class="text-center align-middle p-4 border-0">
                                    @php
                                        $totalPagado = $asistente->payments->sum('amount');
                                    @endphp
                                    ${{ number_format($totalPagado, 0, ',', '.') }}
                                </x-base.table.td>

                                <x-base.table.td class="text-center align-middle p-4 border-0">
                                    <div
                                        class="alert bg-{{ $asistente->has_entered ? 'success' : 'warning' }} text-slate-900 p-2 rounded-md">
                                        {{ $asistente->has_entered ? 'Entrada' : 'No Entrada' }}
                                    </div>
                                </x-base.table.td>

                                <x-base.table.td class="text-center align-middle p-4 border-0 rounded-r-lg">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-base.tippy content="Gestión de Pagos">
                                            <a href="{{ route('eventAssistant.payment', ['id' => $asistente->id]) }}"
                                                class="text-warning hover:text-warning-dark">
                                                <x-base.lucide icon="credit-card" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>

                                        <x-base.tippy content="Registrar Pago">
                                            <a href="{{ route('payments.create', $asistente->id) }}"
                                                class="btn btn-primary btn-sm py-1 px-2">
                                                Registrar
                                            </a>
                                        </x-base.tippy>
                                    </div>
                                </x-base.table.td>
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
        </div>
        <!-- END: Payment Table -->

        <!-- BEGIN: Delete Confirmation Modal -->
        <x-base.dialog id="delete-confirmation-modal">
            <x-base.dialog.panel>
                <div class="p-5 text-center">
                    <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="XCircle" />
                    <div class="mt-5 text-3xl">¿Está seguro?</div>
                    <div class="mt-2 text-slate-500">
                        ¿Realmente desea eliminar estos registros? <br />
                        Este proceso no se puede deshacer.
                    </div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancel
                    </x-base.button>

                    <!-- Formulario de eliminación -->
                    <form id="delete-form" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <x-base.button class="w-24" type="submit" variant="danger">
                            Delete
                        </x-base.button>
                    </form>
                </div>
            </x-base.dialog.panel>
        </x-base.dialog>

        <script>
            function setDeleteAction(element) {
                // Obtener el ID desde el atributo data-id
                const id = element.getAttribute('data-id');
                // Establecer la acción del formulario con la ruta dinámica
                const form = document.getElementById('delete-form');
                form.action = `/assistants/delete/${id}`;
            }
            document.getElementById('generateCouponsButton').addEventListener('click', function() {
                const eventId = {{ $idEvent }}; // ID del evento (modificar dinámicamente si es necesario)
                const numberOfCoupons = document.getElementById('ticketType')
                    .value; // Número de cupones que quieres generar
                const ticketTypeId = document.getElementById('ticketType').value; // Obtener el ticket seleccionado
                fetch("{{ route('generateCoupons') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            event_id: eventId,
                            number_of_coupons: numberOfCoupons,
                            ticket_type_id: ticketTypeId // Enviar el ID del ticket
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Aquí puedes recargar los cupones y actualizar la tabla
                        loadCoupons(eventId);
                    })
                    .catch(error => console.error('Error:', error));
            });
            // Función para cargar los cupones y actualizar la tabla
            function loadCoupons(eventId) {
                fetch(`/get-coupons/${eventId}`)
                    .then(response => response.json())
                    .then(coupons => {
                        let tableBody = document.getElementById('couponsTableBody');
                        tableBody.innerHTML = ''; // Limpiar la tabla
                        coupons.forEach((coupon, index) => {
                            let row = `
                    <tr>
                        <td class="px-4 py-2">${index + 1}</td>
                        <td class="px-4 py-2">${coupon.numeric_code}</td>
                        <td class="px-4 py-2">${coupon.ticket_type.name}</td>
                        <td class="px-4 py-2">${coupon.is_consumed ? 'Sí' : 'No'}</td>
                    </tr>
                `;
                            tableBody.insertAdjacentHTML('beforeend', row);
                        });
                    });
            }

            // Inicializar carga de cupones
            @if (isset($idEvent))
                loadCoupons({{ $idEvent }});
            @endif
        </script>

        <!-- END: Delete Confirmation Modal -->

        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap items-center sm:flex-row sm:flex-nowrap">
            {{ $asistentes->links() }}
        </div>
        <!-- END: Pagination -->
    </div>
@endsection
