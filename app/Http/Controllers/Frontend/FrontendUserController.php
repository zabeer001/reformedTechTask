<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendUserController extends Controller
{
    public function index()
    {
        return view('dashboard.users.index.index');
    }
    public function create()
    {
        return view('dashboard.users.create');
    }
    public function edit()
    {
        return view('dashboard.users.edit');
    }
}
