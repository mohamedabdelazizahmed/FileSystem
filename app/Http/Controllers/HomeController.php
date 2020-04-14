<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {   
        return view('home');
    }
    public function downloadFile()
    {
        $file = public_path().'\storage\upload\merged.pdf';
        $headers = ['Content-Type: application/pdf'];
        return response()->download($file, 'result.pdf', $headers);
    }
}
