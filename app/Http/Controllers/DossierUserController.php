<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Piece;

class DossierUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create()
    {
        return view('admin.pages.user.create');
    }

    public function edit(User $user)
    {
        return view('admin.pages.user.edit', compact('user'));
    }

}
