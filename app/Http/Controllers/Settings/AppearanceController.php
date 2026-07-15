<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppearanceController extends Controller
{
    /**
     * Show the appearance settings form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Appearance');
    }
}
