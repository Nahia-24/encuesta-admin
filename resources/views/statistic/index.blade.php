@extends('themes.base')

@section('subcontent')
    <h2 class="text-2xl font-bold mb-6">
        Estadísticas — {{ $survey->title }}
    </h2>

    <div class="rounded-xl bg-white dark:bg-darkmode-600 shadow p-6">
        <p class="text-lg">
            <strong>Total de respuestas:</strong> {{ $survey->responses_count }}
        </p>
        {{-- Agrega más métricas aquí cuando las tengas --}}
    </div>
@endsection
