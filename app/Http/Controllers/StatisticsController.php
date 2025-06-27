<?php

namespace App\Http\Controllers;

use App\Models\Survey;

class StatisticsController extends Controller
{
    /**
     * Estadísticas de UNA encuesta.
     */
    public function show(Survey $survey)
    {
        // -- total de respuestas (y cualquier otro dato que quieras)
        $survey->loadCount('responses');

        return view('surveys.stats', compact('survey'));
    }
}
