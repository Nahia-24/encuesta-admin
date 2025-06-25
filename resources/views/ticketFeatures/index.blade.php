@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
@endsection

@section('subcontent')
    <h2 class="intro-y mt-10 text-lg font-medium">Lista de Tickets</h2>
    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 mt-2 flex flex-wrap items-center sm:flex-nowrap">
            <a href="{{ route('ticketFeatures.create') }}">
                <x-base.button class="mr-2 shadow-md" variant="primary">
                    Crear nuevo Ticket
                </x-base.button>
            </a>
            <x-base.menu>
                <x-base.menu.button class="!box px-2" as="x-base.button">
                    <span class="flex h-5 w-5 items-center justify-center">
                        <x-base.lucide class="h-4 w-4" icon="Plus" />
                    </span>
                </x-base.menu.button>
                <x-base.menu.items class="w-40">
                    <x-base.menu.item>
                        <x-base.lucide class="mr-2 h-4 w-4" icon="Printer" /> Imprimir
                    </x-base.menu.item>
                    <x-base.menu.item>
                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Exportar a
                        Excel
                    </x-base.menu.item>
                    <x-base.menu.item>
                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" /> Exportar a
                        PDF
                    </x-base.menu.item>
                </x-base.menu.items>
            </x-base.menu>
            {{-- <div class="mx-auto hidden text-slate-500 md:block">
                Showing {1} to {10} of {150} entries
            </div> --}}
            <div class="mt-3 w-full sm:ml-auto sm:mt-0 sm:w-auto md:ml-0">
                <div class="relative w-56 text-slate-500">
                    <form method="GET" action="{{ route('ticketFeatures.index') }}"
                        class="mt-3 w-full sm:ml-auto sm:mt-0 sm:w-auto md:ml-0">
                        <div class="relative w-56 text-slate-500">
                            <x-base.form-input name="search" value="{{ request('search') }}" class="!box w-56 pr-10"
                                type="text" placeholder="Search..." />
                            <button type="submit" class="absolute inset-y-0 right-0 my-auto mr-3 h-4 w-4">
                                <x-base.lucide icon="Search" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if ($ticketTypesWithFeaturedFeatures->count())
            <div class="intro-y col-span-12 mt-10">
                <h3 class="text-lg font-semibold mb-4">Tickets con Características Destacadas</h3>
                <div class="overflow-x-auto">
                    <x-base.table class="border-separate border-spacing-y-[10px]">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="whitespace-nowrap">ID</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Nombre</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Precio</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Características</x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @foreach ($ticketTypesWithFeaturedFeatures as $type)
                                <x-base.table.tr>
                                    <x-base.table.td>{{ $type->id }}</x-base.table.td>
                                    <x-base.table.td>{{ $type->name }}</x-base.table.td>
                                    <x-base.table.td>${{ $type->formattedPrice() }}</x-base.table.td>
                                    <x-base.table.td>
                                        <ul class="list-disc ml-4">
                                            @foreach ($type->features as $feature)
                                                @if ($feature->featured)
                                                    <li>{{ $feature->name }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </x-base.table.td>
                                </x-base.table.tr>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
            </div>
        @endif

        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <x-base.table class="-mt-2 border-separate border-spacing-y-[10px]">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.th class="whitespace-nowrap border-b-0">ID</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Ticket</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Capacidad</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Acciones</x-base.table.th>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @foreach ($tickets as $ticket)
                        @php
                            $ticketInfo = $dataGeneral[$ticket->id] ?? ['availableTickets' => 0, 'capacity' => 0];
                        @endphp
                        <x-base.table.tr class="intro-x bg-white dark:bg-darkmode-600 rounded-[0.6rem] shadow-sm">
                            <!-- ID -->
                            <x-base.table.td class="border-y-0 first:rounded-l-[0.6rem] align-middle">
                                {{ $ticket->id }}
                            </x-base.table.td>

                            <!-- Nombre del Ticket -->
                            <x-base.table.td class="text-center border-y-0 align-middle">
                                {{ $ticket->name }}
                            </x-base.table.td>

                            <!-- Capacidad -->
                            <x-base.table.td class="text-center border-y-0 align-middle">
                                {{ $ticket->capacity }}
                            </x-base.table.td>

                            <!-- Acciones -->
                            <x-base.table.td class="text-center border-y-0 align-middle">
                                <div class="flex justify-center gap-3">
                                    <a href="{{ route('ticketFeatures.edit', ['id' => $ticket->id]) }}"
                                        class="flex items-center text-primary font-medium hover:underline">
                                        <x-base.lucide class="w-4 h-4 mr-1" icon="Edit" /> Editar
                                    </a>
                                    <a href="{{ route('ticketFeatures.delete', ['id' => $ticket->id]) }}"
                                        class="flex items-center text-danger font-medium hover:underline"
                                        data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal">
                                        <x-base.lucide class="w-4 h-4 mr-1" icon="Trash" /> Borrar
                                    </a>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @endforeach
                </x-base.table.tbody>
            </x-base.table>
        </div>
        <!-- END: Data List -->

        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap items-center sm:flex-row sm:flex-nowrap mt-4">
            {{ $tickets->withQueryString()->links() }}
        </div>
        <!-- END: Pagination -->

    </div>
    <!-- BEGIN: Delete Confirmation Modal -->
    <x-base.dialog id="delete-confirmation-modal">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="XCircle" />
                <div class="mt-5 text-3xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete these records? <br />
                    This process cannot be undone.
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                    Cancel
                </x-base.button>
                <x-base.button class="w-24" type="button" variant="danger">
                    Delete
                </x-base.button>
            </div>
        </x-base.dialog.panel>
    </x-base.dialog>
    <!-- END: Delete Confirmation Modal -->
@endsection

@section('subscript')
@endsection
