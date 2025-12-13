<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendEmployeeController extends Controller
{
    public function index()
    {
        return view('dashboard.employees.index');
    }
    public function create()
    {
        return view('dashboard.employees.create');
    }
    public function edit()
    {
        return view('dashboard.employees.edit');
    }
}
