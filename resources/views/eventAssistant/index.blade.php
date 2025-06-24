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
        <h2 class="text-lg font-medium">Lista de Asistentes</h2>
        <a href="{{ route('event.index') }}">
            <x-base.button class="shadow-md h-9 px-8 text-sm" variant="primary">
                Volver a Eventos
            </x-base.button>
        </a>

    </div>

    <div class="">
        <script>
            const chartData = @json($dataGeneral);
            const ticketData = @json($ticketsInfo); // Pasar información de tickets
        </script>
        <!-- BEGIN: Multiple Item -->
        <x-base.preview-component class="intro-y box mt-5">
            <div class="flex flex-col items-center border-b border-slate-200/60 p-5 dark:border-darkmode-400 sm:flex-row">
                <h2 class="mr-auto text-base font-medium">
                    Informe Grafico de entradas
                </h2>
            </div>
            <div class="p-5">
                <x-base.preview>
                    <div class="mx-6">
                        <x-base.tiny-slider id="multiple-item-slider" config="multiple-items">
                            <div class="px-2">
                                <div class="h-full rounded-md bg-slate-100 dark:bg-darkmode-400">
                                    <div>ASISTENCIA GENERAL</div>
                                    <div class="intro-y box mt-5 p-5 col-span-12 items-center">
                                        <div class="mt-3">
                                            <x-chart-assistants />
                                        </div>
                                        <div class="mx-auto mt-8 w-52 sm:w-auto">
                                            <div class="flex items-center">
                                                <div class="mr-3 h-2 w-2 rounded-full bg-primary"></div>
                                                <span class="truncate">Entradas registradas</span>
                                                <span class="ml-auto font-medium">{{ $dataGeneral['soldTickets'] }}
                                                    ({{ round(($dataGeneral['soldTickets'] / $dataGeneral['capacity']) * 100, 2) }}%)</span>
                                            </div>
                                            <div class="mt-4 flex items-center">
                                                <div class="mr-3 h-2 w-2 rounded-full bg-pending"></div>
                                                <span class="truncate">Entradas Disponibles</span>
                                                <span class="ml-auto font-medium">{{ $dataGeneral['availableTickets'] }}
                                                    ({{ round(($dataGeneral['availableTickets'] / $dataGeneral['capacity']) * 100, 2) }}%)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @foreach ($ticketsInfo as $ticketInfo)
                                <div class="px-2">
                                    <div class="h-full rounded-md bg-slate-100 dark:bg-darkmode-400">
                                        <div>{{ $ticketInfo['name'] }}</div>
                                        <div class="intro-y box mt-5 p-5 col-span-12 items-center">
                                            <div class="mt-3">
                                                {{-- <x-chart-assistants height="h-[213px]" /> --}}
                                                <div class="chart-container">
                                                    <x-base.chart id="report-pie-chart-{{ $ticketInfo['ticket_type_id'] }}"
                                                        class="chart"></x-base.chart>
                                                </div>
                                            </div>
                                            <div class="mx-auto mt-8 w-52 sm:w-auto">
                                                <div class="flex items-center">
                                                    <div class="mr-3 h-2 w-2 rounded-full bg-primary"></div>
                                                    <span class="truncate">Entradas registradas</span>
                                                    <span class="ml-auto font-medium">{{ $ticketInfo['soldTickets'] }}
                                                        ({{ round(($ticketInfo['soldTickets'] / $ticketInfo['capacity']) * 100, 2) }}%)
                                                    </span>
                                                </div>
                                                <div class="mt-4 flex items-center">
                                                    <div class="mr-3 h-2 w-2 rounded-full bg-pending"></div>
                                                    <span class="truncate">Entradas Disponibles</span>
                                                    <span class="ml-auto font-medium">{{ $ticketInfo['availableTickets'] }}
                                                        ({{ round(($ticketInfo['availableTickets'] / $ticketInfo['capacity']) * 100, 2) }}%)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </x-base.tiny-slider>
                    </div>
                </x-base.preview>
            </div>
        </x-base.preview-component>
        <!-- END: Multiple Item -->

        <!-- BEGIN: Actions -->

        <div class="flex flex-wrap gap-2 mt-6 items-center">

            {{-- Cargue Asistente --}}
            <x-base.menu>
                <x-base.menu.button class="!box" as="x-base.button">
                    <span class="flex items-center justify-center">
                        <x-base.lucide class="h-4 w-4" icon="Plus" />
                        Cargue Asistente
                    </span>
                </x-base.menu.button>
                <x-base.menu.items class="w-40">
                    <x-base.menu.item>
                        <a href="{{ route('eventAssistant.singleCreateForm', ['idEvent' => $idEvent]) }}">
                            <x-base.button class="mr-2 shadow-md" variant="secondary">Crear Manualmente</x-base.button>
                        </a>
                    </x-base.menu.item>
                    <x-base.menu.item>
                        <a href="{{ route('eventAssistant.massAssign', ['idEvent' => $idEvent]) }}">
                            <x-base.button class="mr-2 shadow-md" variant="primary">Crear Masivamente</x-base.button>
                        </a>
                    </x-base.menu.item>
                </x-base.menu.items>
            </x-base.menu>

            {{-- Asignar pagos --}}
            <a href="{{ route('eventAssistant.massPayload', ['idEvent' => $idEvent]) }}">
                <x-base.button class="shadow-md h-10" variant="primary">Asignar Pagos Masivamente</x-base.button>
            </a>

            {{-- Códigos de cortesía --}}
            <x-base.button class="shadow-md h-10" data-tw-toggle="modal"
                data-tw-target="#superlarge-slide-over-size-preview" as="a" href="#" variant="primary">
                Códigos de cortesía
            </x-base.button>

            {{-- Buscador --}}
            <form method="GET" action="{{ route('eventAssistant.index', ['idEvent' => $idEvent]) }}">
                <div class="relative w-56 text-slate-500">
                    <input type="text" name="search" class="!box w-56 pr-10 h-10"
                        value="{{ request()->input('search') }}" placeholder="Buscar..." />
                    <button type="submit" class="absolute inset-y-0 right-0 my-auto mr-3 h-4 w-4">
                        <x-base.lucide icon="Search" />
                    </button>
                </div>
            </form>

            {{-- Búsqueda específica --}}
            <a href="{{ route('eventAssistant.specificSearch', ['idEvent' => $idEvent]) }}">
                <x-base.button class="shadow-md h-10" variant="primary">Búsqueda específica</x-base.button>
            </a>

            {{-- Reporte (como menú) --}}
            <x-base.menu>
                <x-base.menu.button class="!box" as="x-base.button">
                    <span class="flex items-center justify-center">
                        <x-base.lucide class="h-4 w-4" icon="Plus" />
                        Reporte
                    </span>
                </x-base.menu.button>
                <x-base.menu.items class="w-40">
                    <x-base.menu.item>
                        <a class="mr-3" target="_blank"
                            href="{{ route('eventAssistant.exportExcel', [
                                'idEvent' => $idEvent,
                                'search' => request()->input('search'),
                                'additionalParameters' => request()->input('additionalParameters', []),
                            ]) }}">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Exportar a Excel
                        </a>
                    </x-base.menu.item>
                </x-base.menu.items>
            </x-base.menu>

            {{-- Reporte Pagos (como menú) --}}
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
                            <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Pagos Realizados (EXCEL)
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

        </div>
        <!-- END: Actions -->

        <!-- BEGIN: Table (Versión Mejorada) -->
        @php
            $selectedFields = json_decode($event->registration_parameters, true) ?? [];
            $additionalParameters = json_decode($event->additionalParameters, true) ?? [];
        @endphp
        <div class="intro-y col-span-12 bg-white dark:bg-darkmode-700 rounded-lg shadow-lg p-5 mt-8">
            <div class="overflow-x-auto">
                <!-- Mantenemos la estructura base pero ajustamos las clases -->
                <x-base.table class="-mt-2 border-separate border-spacing-y-2 min-w-full">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <!-- CAMBIO: Estilo del encabezado más limpio y profesional -->
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 140px;">Acciones</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 180px;">Nombres</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 180px;">Apellidos</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 140px;">Tipo Doc.</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 160px;">Número Doc.</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 160px;">Celular</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 280px;">Correo</x-base.table.th>
                            <!-- ... puedes aplicar la misma clase a los demás TH ... -->
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 160px;">Fecha de Nac.</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 160px;">Departamento</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 160px;">Ciudad</x-base.table.th>
                            @foreach ($additionalParameters as $parameter)
                                <x-base.table.th
                                    class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                    style="min-width: 180px;">
                                    {{ ucfirst(str_replace('_', ' ', $parameter['name'])) }}
                                </x-base.table.th>
                            @endforeach
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 160px;">Tipo de ticket</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 140px;">Entrada</x-base.table.th>
                            <x-base.table.th
                                class="border-b-0 whitespace-nowrap text-center text-slate-500 uppercase tracking-wider font-bold"
                                style="min-width: 140px;">Ticket</x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($asistentes as $asistente)
                            <!-- CAMBIO: Fila con fondo, sombra, bordes redondeados y efecto hover -->
                            <x-base.table.tr
                                class="intro-x bg-white dark:bg-darkmode-600 shadow-md rounded-lg hover:shadow-xl transition-shadow duration-200">

                                <!-- CAMBIO: Mejor padding, alineación y espaciado en los íconos de acción -->
                                <x-base.table.td class="text-center align-middle p-4 border-0 rounded-l-lg"
                                    style="min-width: 140px;">
                                    <div class="flex items-center justify-center gap-x-3">
                                        <x-base.tippy content="Mostrar Codigo QR">
                                            <a class="text-info hover:opacity-75"
                                                href="{{ route('eventAssistant.qr', ['id' => $asistente->id]) }}">
                                                <x-base.lucide icon="QrCode" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Generar PDF Informativo">
                                            <a class="text-info hover:opacity-75"
                                                href="{{ route('eventAssistant.pdf', ['id' => $asistente->id]) }}"
                                                target="_blank">
                                                <x-base.lucide icon="FileText" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Generar Boleta PDF">
                                            <a class="text-info hover:opacity-75"
                                                href="{{ route('eventAssistant.getPDFTicket', ['id' => $asistente->id]) }}"
                                                target="_blank">
                                                <x-base.lucide icon="FileText" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Enviar Correo">
                                            <a class="text-info hover:opacity-75"
                                                href="{{ route('eventAssistant.sendEmail', ['id' => $asistente->id]) }}"
                                                target="_blank">
                                                <x-base.lucide icon="send" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Gestion de Pagos">
                                            <a class="text-warning hover:opacity-75"
                                                href="{{ route('eventAssistant.payment', ['id' => $asistente->id]) }}"
                                                target="_blank">
                                                <x-base.lucide icon="credit-card" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Editar">
                                            <a class="hover:opacity-75"
                                                href="{{ route('eventAssistant.singleUpdateForm', ['idEventAssistant' => $asistente->id]) }}">
                                                <x-base.lucide icon="CheckSquare" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>
                                        <x-base.tippy content="Borrar">
                                            <a class="text-danger hover:opacity-75" data-tw-toggle="modal"
                                                data-tw-target="#delete-confirmation-modal"
                                                data-id="{{ $asistente->id }}" onclick="setDeleteAction(this)">
                                                <x-base.lucide icon="Trash" class="w-5 h-5" />
                                            </a>
                                        </x-base.tippy>
                                    </div>
                                </x-base.table.td>

                                <!-- CAMBIO: Celdas con mejor padding y alineación vertical -->
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 180px;">{{ $asistente->user->name }}</x-base.table.td>
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 180px;">{{ $asistente->user->lastname }}</x-base.table.td>
                                <!-- ... aplica 'align-middle p-4 border-0' a las demás celdas de datos ... -->
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 140px;">{{ $asistente->user->type_document }}</x-base.table.td>
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 160px;">{{ $asistente->user->document_number }}</x-base.table.td>
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 160px;">{{ $asistente->user->phone }}</x-base.table.td>
                                <x-base.table.td class="text-center align-middle p-4 border-0 break-words"
                                    style="min-width: 280px; white-space: normal;">{{ $asistente->user->email }}</x-base.table.td>
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 160px;">{{ $asistente->user->birth_date }}</x-base.table.td>
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 160px;">{{ $asistente->department?->name ?? '-' }}</x-base.table.td>
                                <x-base.table.td class="text-center align-middle p-4 border-0"
                                    style="min-width: 160px;">{{ $asistente->user->city?->name ?? '-' }}</x-base.table.td>
                                @foreach ($additionalParameters as $parameter)
                                    @php
                                        $userParameter = $asistente->eventParameters->firstWhere(
                                            'additional_parameter_id',
                                            $parameter['id'],
                                        );
                                    @endphp
                                    <x-base.table.td class="text-center align-middle p-4 border-0"
                                        style="min-width: 180px;">
                                        {{ $userParameter ? $userParameter->value : '-' }}
                                    </x-base.table.td>
                                @endforeach
                                <x-base.table.td class="text-center align-middle p-4 border-0" style="min-width: 160px;">
                                    {{ $asistente->ticketType?->name ?? 'SIN REGISTRO' }}
                                </x-base.table.td>

                                <!-- REVERTIDO: Columna "Entrada" con el estilo original de alert -->
                                <x-base.table.td class="text-center align-middle p-4 border-0" style="min-width: 140px">
                                    @if ($asistente->has_entered)
                                        <div
                                            class="alert bg-success text-slate-900 w-32 min-h-[36px] flex items-center justify-center text-sm font-medium">
                                            Entrada
                                        </div>
                                    @else
                                        <div
                                            class="alert bg-warning text-slate-900 w-32 min-h-[36px] flex items-center justify-center text-sm font-medium">
                                            No Entrada
                                        </div>
                                    @endif
                                </x-base.table.td>

                                <!-- REVERTIDO: Columna "Ticket" con el estilo original de alert y esquina redondeada -->
                                <x-base.table.td class="text-center align-middle p-4 border-0 rounded-r-lg"
                                    style="min-width: 140px;">
                                    @if ($asistente->is_paid)
                                        <div
                                            class="alert bg-success text-slate-900 w-32 min-h-[36px] flex items-center justify-center text-sm font-medium">
                                            Pagado
                                        </div>
                                    @else
                                        @if ($asistente->totalPayments() == 0)
                                            <div
                                                class="alert bg-danger text-white w-32 min-h-[36px] flex items-center justify-center text-sm font-medium">
                                                No Pagado
                                            </div>
                                        @else
                                            <div
                                                class="alert bg-warning text-slate-900 w-32 min-h-[36px] flex items-center justify-center text-sm font-medium">
                                                Pendiente
                                            </div>
                                        @endif
                                    @endif
                                </x-base.table.td>

                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
        </div>
        <!-- END: Table -->

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

        <x-base.slideover id="superlarge-slide-over-size-preview" size="xl">
            <x-base.slideover.panel>
                <x-base.slideover.title class="p-5">
                    <h2 class="mr-auto text-base font-medium">
                        Códigos de cortesía
                    </h2>
                </x-base.slideover.title>
                <x-base.slideover.description>
                    <div class="flex justify-between mb-4">
                        <!-- Select para elegir el tipo de ticket -->
                        <div class="w-full">
                            <label for="ticketType" class="block text-sm font-medium text-gray-700">Seleccionar
                                Ticket</label>
                            <select id="ticketType" name="ticketType"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                @foreach ($tickets as $ticket)
                                    <option value="{{ $ticket->id }}">{{ $ticket->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full">
                            <label for="numberOfCoupons" class="block text-sm font-medium text-gray-700">N cupones</label>
                            <input type="number" id="numberOfCoupons">
                        </div>
                        <!-- Botón para generar nuevos códigos -->
                        <button id="generateCouponsButton" class="btn btn-primary ml-4 mt-4">
                            Generar nuevos códigos
                        </button>
                    </div>

                    <!-- Tabla para mostrar los cupones -->
                    <div class="overflow-auto">
                        <table class="table-auto w-full text-center">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">N°</th>
                                    <th class="px-4 py-2">Código</th>
                                    <th class="px-4 py-2">Ticket</th>
                                    <th class="px-4 py-2">Consumido</th>
                                </tr>
                            </thead>
                            <tbody id="couponsTableBody">
                                <!-- Aquí se cargan dinámicamente los cupones -->
                            </tbody>
                        </table>
                    </div>
                </x-base.slideover.description>
            </x-base.slideover.panel>
        </x-base.slideover>

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
                    .value;; // Número de cupones que quieres generar
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
            loadCoupons({{ $idEvent }});
        </script>

        <!-- END: Delete Confirmation Modal -->

        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap items-center sm:flex-row sm:flex-nowrap">
            {{ $asistentes->links() }}
        </div>
        <!-- END: Pagination -->
    </div>
@endsection
