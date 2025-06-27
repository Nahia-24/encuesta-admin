@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>INICIO</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5">
                    <div class="text-base text-slate-500">Total Encuestas</div>
                    <div class="text-3xl font-bold">{{ $totalSurveys }}</div>
                </div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
            <div class="report-box zoom-in">
                <div class="box p-5">
                    <div class="text-base text-slate-500">Total Respuestas</div>
                    <div class="text-3xl font-bold">{{ $totalResponses }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de encuestas recientes --}}
    <div class="mt-10">
        <h2 class="text-lg font-medium mb-5">Encuestas recientes</h2>
        <ul class="list-disc pl-5">
            @foreach ($recentSurveys as $survey)
                <li>{{ $survey->title }} — {{ $survey->created_at->format('d/m/Y') }}</li>
            @endforeach
        </ul>
    </div>
@endsection
