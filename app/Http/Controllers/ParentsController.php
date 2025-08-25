<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ParentsController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('parents');
    }
}
