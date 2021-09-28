<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControlePontoController extends Controller
{
    public function index(Request $request){
        return view('g.controle-ponto.index');
    }
}
