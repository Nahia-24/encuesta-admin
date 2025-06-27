<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\View\View;

class AjusteGeneralController extends Controller
{
    public function index(): View
    {
        $roles = Role::all();
    }

}
