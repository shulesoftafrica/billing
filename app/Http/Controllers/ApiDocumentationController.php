<?php

namespace App\Http\Controllers;

class ApiDocumentationController extends Controller
{
    public function index()
    {
        return view('docs.index');
    }
}
