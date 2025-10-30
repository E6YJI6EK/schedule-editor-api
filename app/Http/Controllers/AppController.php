<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index() {
        return successResponse("I AM ALIVE!!!");
    }
}
