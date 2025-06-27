@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>INICIO</title>
@endsection

@section('subcontent')
    <div class="col-span-12 mt-8">
        <div class="intro-y flex h-10 items-center">
            <h2 class="mr-5 truncate text-lg font-medium">Estadisticas Generales</h2>
            <a class="ml-auto flex items-center text-primary" href="">
                <x-base.lucide class="mr-3 h-4 w-4" icon="RefreshCcw" /> Recargar Datos
            </a>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            {{-- Tarjeta: Total Encuestas --}}
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                <div class="report-box zoom-in">
                    <div class="box p-5">
                        <div class="flex">
                            <x-base.lucide class="h-[28px] w-[28px] text-primary" icon="ClipboardList" />
                        </div>
                        <div class="mt-6 text-3xl font-bold">{{ $totalSurveys }}</div>
                        <div class="mt-1 text-base text-slate-500">Total Encuestas</div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta: Total Respuestas --}}
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                <div class="report-box zoom-in">
                    <div class="box p-5">
                        <div class="flex">
                            <x-base.lucide class="h-[28px] w-[28px] text-success" icon="CheckSquare" />
                        </div>
                        <div class="mt-6 text-3xl font-bold">{{ $totalResponses }}</div>
                        <div class="mt-1 text-base text-slate-500">Total Respuestas</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de encuestas recientes --}}
        <div class="intro-y mt-10 box p-5">
            <h2 class="text-lg font-medium mb-4">Encuestas recientes</h2>
            <div class="overflow-x-auto">
                <table class="table table-report">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">TÍTULO</th>
                            <th class="whitespace-nowrap">FECHA DE CREACIÓN</th>
                            <th class="whitespace-nowrap text-center">RESPUESTAS</th>
                            <th class="whitespace-nowrap text-center">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentSurveys as $survey)
                            <tr>
                                <td>{{ $survey->title }}</td>
                                <td>{{ $survey->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $survey->responses_count }}</td>
                                <td class="text-center">
                                    <a href="{{ route('surveys.stats', $survey->id) }}" class="text-primary font-medium">
                                        Ver estadísticas
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-slate-500">No hay encuestas recientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
