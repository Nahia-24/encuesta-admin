@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Usuarios</title>
@endsection

@section('subcontent')
    <h2 class="intro-y mt-10 text-lg font-medium">Lista de usuarios</h2>
    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 mt-2 flex flex-wrap items-center sm:flex-nowrap">
            <!-- Formulario de búsqueda -->
            <form method="GET" action="{{ route('users.index') }}" class="mt-3 w-full sm:ml-auto sm:mt-0 sm:w-auto md:ml-0">
                <div class="relative w-56 text-slate-500">
                    <x-base.form-input name="search" value="{{ request('search') }}" class="!box w-56 pr-10" type="text" placeholder="Search..." />
                    <button type="submit" class="absolute inset-y-0 right-0 my-auto mr-3 h-4 w-4">
                        <x-base.lucide icon="Search" />
                    </button>
                </div>
            </form>
        </div>
        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <x-base.table class="-mt-2 border-separate border-spacing-y-[10px]">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.th class="whitespace-nowrap border-b-0">No.</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0">Nombre Completo</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Telefono</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Role</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Estado</x-base.table.th>
                        <x-base.table.th class="whitespace-nowrap border-b-0 text-center">Acciones</x-base.table.th>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @foreach ($usuarios as $index => $usuario)
                        <x-base.table.tr class="intro-x">

                            <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 text-center shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r dark:bg-darkmode-600">
                                {{ $usuarios->firstItem() + $index }}
                            </x-base.table.td>
                            <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r dark:bg-darkmode-600">
                                <div class="whitespace-nowrap font-medium">{{ $usuario->name }} {{ $usuario->lastname }}</div>
                            </x-base.table.td>
                            <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 text-center shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r dark:bg-darkmode-600">
                                {{ $usuario->phone }}
                            </x-base.table.td>
                            <x-base.table.td class="box rounded-l-none rounded-r-none border-x-0 text-center shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r dark:bg-darkmode-600">
                                {{ isset($usuario->roles[0]) ? $usuario->roles[0]->name : "SIN REGISTRO" }}
                            </x-base.table.td>
                            <x-base.table.td class="box w-40 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r dark:bg-darkmode-600">
                                <div class="flex items-center justify-center {{ $usuario->status ? 'text-success' : 'text-danger' }}">
                                    <x-base.lucide class="mr-2 h-4 w-4" icon="CheckSquare" />
                                    {{ $usuario->status ? 'Activo' : 'Inactivo' }}
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="box w-56 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r dark:bg-darkmode-600">
                                <div class="flex items-center justify-center">
                                    <a class="mr-3 flex items-center" href="{{ route('users.edit', ['id' => $usuario->id]) }}">
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="CheckSquare" /> Editar
                                    </a>
                                    {{-- <a class="flex items-center text-danger" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal" href="#">
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Trash" /> Borrar
                                    </a> --}}
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @endforeach
                </x-base.table.tbody>
            </x-base.table>
        </div>
        <!-- END: Data List -->
        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap items-center sm:flex-row sm:flex-nowrap">
            {{ $usuarios->withQueryString()->links() }}
        </div>
        <!-- END: Pagination -->
    </div>
@endsection
