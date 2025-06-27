@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Encuestas</title>
@endsection

@section('subcontent')
    <h2 class="intro-y mt-10 text-lg font-medium">Lista de Encuestas</h2>
    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 mt-2 flex flex-wrap items-center sm:flex-nowrap">
            <a href="{{ route('surveys.create') }}">
                <x-base.button class="mr-2 shadow-md" variant="primary">
                    Crear nueva Encuesta
                </x-base.button>
            </a>

            <div class="mt-3 w-full sm:ml-auto sm:mt-0 sm:w-auto md:ml-0">
                <form action="{{ route('surveys.index') }}" method="GET">
                    <div class="relative w-56 text-slate-500">
                        <x-base.form-input name="search" class="!box w-56 pr-10" type="text" placeholder="Buscar..."
                            value="{{ request('search') }}" />
                        <button type="submit" class="absolute inset-y-0 right-0 my-auto mr-3 h-4 w-4">
                            <x-base.lucide icon="Search" />
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- BEGIN: Tabla de Encuestas -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <x-base.table class="-mt-2 border-separate border-spacing-y-[10px]">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.th class="text-center">Acciones</x-base.table.th>
                        <x-base.table.th>Título</x-base.table.th>
                        <x-base.table.th>Descripción</x-base.table.th>
                        <x-base.table.th class="text-center">Preguntas</x-base.table.th>
                        <x-base.table.th class="text-center">Respuestas</x-base.table.th>
                        <x-base.table.th class="text-center">Estado</x-base.table.th>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @foreach ($surveys as $survey)
                        <x-base.table.tr>
                            <x-base.table.td class="text-center">
                                <div class="flex items-center justify-center">
                                    <x-base.tippy content="Editar">
                                        <a class="flex items-center mr-3" href="{{ route('surveys.edit', $survey->id) }}">
                                            <x-base.lucide icon="Edit" />
                                        </a>
                                    </x-base.tippy>

                                    <x-base.tippy content="Ver Estadísticas">
                                        <a class="flex items-center mr-3" href="{{ route('surveys.stats', $survey->id) }}">
                                            <x-base.lucide icon="BarChart" />
                                        </a>
                                    </x-base.tippy>

                                    <form method="POST" action="{{ route('surveys.destroy', $survey->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-base.tippy content="Eliminar">
                                            <button class="text-red-500">
                                                <x-base.lucide icon="Trash" />
                                            </button>
                                        </x-base.tippy>
                                    </form>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td>{{ $survey->title }}</x-base.table.td>
                            <x-base.table.td>{{ Str::limit($survey->description, 50) }}</x-base.table.td>
                            <x-base.table.td class="text-center">{{ $survey->questions_count ?? 0 }}</x-base.table.td>
                            <x-base.table.td class="text-center">{{ $survey->responses_count ?? 0 }}</x-base.table.td>
                            <x-base.table.td class="text-center">
                                @if ($survey->status == 'draft')
                                    <span class="text-yellow-600 font-semibold">Borrador</span>
                                @elseif ($survey->status == 'published')
                                    <span class="text-green-600 font-semibold">Publicado</span>
                                @else
                                    <span class="text-red-600 font-semibold">Cerrado</span>
                                @endif
                            </x-base.table.td>
                        </x-base.table.tr>
                    @endforeach
                </x-base.table.tbody>
            </x-base.table>
        </div>
        <!-- END: Tabla de Encuestas -->

        <!-- BEGIN: Paginación -->
        <div class="intro-y col-span-12 flex flex-wrap items-center sm:flex-row sm:flex-nowrap">
            {{ $surveys->withQueryString()->links() }}
        </div>
        <!-- END: Paginación -->
    </div>
@endsection
