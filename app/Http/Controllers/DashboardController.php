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
        $users = User::all();
        $pieces = Piece::all();
        return view('admin.pages.dashboard', compact('users', 'pieces'));
    }

}
