<?php

namespace App\Http\Controllers\Sektoral;

use Illuminate\Http\Request;

/** Beranda publik: dashboard GIS tanpa login + tombol Login. */
class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('sektoral.gis.dark', GisController::dashboardData($request) + [
            'guest' => true,
            'formAction' => route('home'),
        ]);
    }
}
