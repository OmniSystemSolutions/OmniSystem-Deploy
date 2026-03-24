<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProcurementRequestController extends Controller
{
    public function index()
    {
        return view('reports.procurement-request.index');
    }
    
}
