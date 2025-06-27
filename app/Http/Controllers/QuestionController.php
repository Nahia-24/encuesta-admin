<?php

namespace App\Http\Controllers;

use App\Models\{Survey, SurveyQuestion};
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /** Preguntas de una encuesta. */
    public function index(Survey $survey)
    {
        return response()->json(
            $survey->questions()->with('options')->orderBy('order')->get()
        );
    }

    /** Crear pregunta. */
    public function store(Request $request, Survey $survey)
    {
        $data = $request->validate([
            'question_text' => 'required|string',
            'type'          => 'required|in:text,textarea,select,radio,checkbox,date,number',
            'is_required'   => 'boolean',
            'order'         => 'integer|min:0',
        ]);

        $question = $survey->questions()->create($data);

        return response()->json($question->load('options'), 201);
    }

    /** Mostrar una pregunta individual. */
    public function show(SurveyQuestion $question)
    {
        return response()->json($question->load('options'));
    }

    /** Actualizar pregunta. */
    public function update(Request $request, SurveyQuestion $question)
    {
        $question->update(
            $request->validate([
                'question_text' => 'sometimes|required|string',
                'type'          => 'in:text,textarea,select,radio,checkbox,date,number',
                'is_required'   => 'boolean',
                'order'         => 'integer|min:0',
            ])
        );

        return response()->json($question->load('options'));
    }

    /** Eliminar pregunta (cascade elimina opciones). */
    public function destroy(SurveyQuestion $question)
    {
        $question->delete();

        return response()->noContent();
    }
}
