<?php

namespace App\Http\Controllers;

use App\Models\{Survey, SurveyResponse};
use Illuminate\Http\Request;

class SurveyResponseController extends Controller
{
    /** Listar respuestas de una encuesta. */
    public function index(Survey $survey)
    {
        return response()->json(
            $survey->responses()->with('answers')->paginate(20)
        );
    }

    /** Mostrar detalle de una respuesta. */
    public function show(SurveyResponse $surveyResponse)
    {
        return response()->json(
            $surveyResponse->load('answers.question')
        );
    }

    /** Eliminar una respuesta. */
    public function destroy(SurveyResponse $surveyResponse)
    {
        $surveyResponse->delete();

        return response()->noContent();
    }
}
