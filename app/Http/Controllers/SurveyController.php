<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /** Listado paginado de encuestas. */
    public function index()
    {
        $surveys = Survey::withCount(['questions', 'responses'])
            ->latest()
            ->paginate(10);

        return view('surveys.index', compact('surveys'));
    }

    public function create()
    {
        return view('surveys.create'); // o la ruta correcta de tu vista de creación
    }

    /** Guardar nueva encuesta (status = draft por defecto). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $survey = $request->user()->surveys()->create($data + ['status' => 'draft']);

        return response()->json($survey, 201);
    }

    /** Mostrar detalles + preguntas + opciones. */
    public function show(Survey $survey)
    {
        return response()->json(
            $survey->load('questions.options')
        );
    }

    /** Actualizar encuesta. */
    public function update(Request $request, Survey $survey)
    {
        $survey->update(
            $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status'      => 'in:draft,published,closed',
            ])
        );

        return response()->json($survey);
    }

    /** Eliminar encuesta. */
    public function destroy(Survey $survey)
    {
        $survey->delete();

        return response()->noContent();
    }
}
