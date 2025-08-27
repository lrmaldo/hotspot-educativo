<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotspotController extends Controller
{
    /**
     * Muestra la página del hotspot con la trivia (wrapper de Livewire component).
     * Mikrotik puede anexar parámetros como gw, mac, ip al redirect.
     */
    public function __invoke(Request $request)
    {
        // Los parámetros se pueden usar en el componente vía request()->query(...)
        return view('hotspot');
    }
}
