<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LayoutController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function switch(string $activeLayout): RedirectResponse
        {
            session(['activeLayout' => $activeLayout]);
            return redirect('/');
        }   
}
