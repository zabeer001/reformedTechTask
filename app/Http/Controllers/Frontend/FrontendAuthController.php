<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontendAuthController extends Controller
{
    
    public function signin()
    {
        return view('frontend.sign-in');
    }
}
