<?php

namespace App\Http\Controllers;

use App\Models\{SurveyQuestion, Survey};
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{
    /** Resumen por pregunta (conteo de opciones seleccionadas). */
    public function summary(SurveyQuestion $question)
    {
        if (!in_array($question->type, ['select', 'radio', 'checkbox'])) {
            return response()->json(['error' => 'Sólo preguntas de opción múltiple'], 400);
        }

        $data = DB::table('survey_answers')
            ->select('answer', DB::raw('COUNT(*) as total'))
            ->where('survey_question_id', $question->id)
            ->groupBy('answer')
            ->get();

        return response()->json($data);
    }

    /** Respuestas abiertas (texto) para una pregunta tipo text|textarea. */
    public function openResponses(SurveyQuestion $question)
    {
        $answers = $question->answers()->select('answer', 'created_at')->latest()->paginate(50);

        return response()->json($answers);
    }

    /** Estadísticas globales de una encuesta (total respuestas, tasa de completitud). */
    public function surveyStats(Survey $survey)
    {
        $totalResponses = $survey->responses()->count();
        $questions      = $survey->questions()->count();

        return response()->json([
            'survey_id'       => $survey->id,
            'responses'       => $totalResponses,
            'questions'       => $questions,
            'answers_avg'     => $totalResponses ? 
                                 round(
                                     DB::table('survey_answers')
                                        ->whereIn('survey_response_id', $survey->responses->pluck('id'))
                                        ->count() / $totalResponses,
                                     1
                                 ) : 0,
        ]);
    }
}
