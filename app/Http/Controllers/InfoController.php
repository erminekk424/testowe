<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class InfoController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('info');
    }
}
