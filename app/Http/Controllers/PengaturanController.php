<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // <-- TAMBAHKAN INI

class PengaturanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('pengaturan.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user(); // <-- TAMBAHKAN ANOTASI INI
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save(); // <-- SEKARANG IDE MENGENALI

        return redirect()->route('pengaturan')->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user(); // <-- TAMBAHKAN ANOTASI INI

        // Cek password lama
        if (!Auth::attempt(['email' => $user->email, 'password' => $request->current_password])) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->password = bcrypt($request->password);
        $user->save(); // <-- SEKARANG IDE MENGENALI

        return redirect()->route('pengaturan')->with('success', 'Password berhasil diperbarui!');
    }
}