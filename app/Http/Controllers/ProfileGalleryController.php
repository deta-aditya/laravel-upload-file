<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileGalleryController extends Controller
{
    function __invoke(Request $request)
    {
        dd($request->all());
    }
}
