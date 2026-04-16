<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DashboardController - Tripmo
 * untuk halaman utama setelah login.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard
     */
    public function index()
{
    $user = Auth::user();
    $postingan = \App\Models\Postingan::where('user_id', $user->id)
                    ->with('photos')
                    ->latest()
                    ->get();
    return view('dashboard.index', compact('user', 'postingan'));
}
}
