<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ThemeController extends Controller
{
    public function switch($activeTheme)
    {
        // Guarda el tema en sesión (puedes adaptarlo)
        Session::put('activeTheme', $activeTheme);

        // Redirecciona a la página anterior o a /home
        return redirect()->back();
    }
}


















