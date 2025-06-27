<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyResponse;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalSurveys = Survey::count();
        $totalResponses = SurveyResponse::count();
        $recentSurveys = Survey::latest()->take(5)->get();

        return view('home', compact('totalSurveys', 'totalResponses', 'recentSurveys'));
    }
}
