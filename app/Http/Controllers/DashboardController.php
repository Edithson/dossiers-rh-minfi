<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Piece;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.pages.dashboard');
    }

}
