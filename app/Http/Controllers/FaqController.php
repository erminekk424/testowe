<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class FaqController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('faq');
    }
}
