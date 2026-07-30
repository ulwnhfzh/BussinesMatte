<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'terms' => 'accepted',
        ]);

        DB::beginTransaction();

        try {
            // Membuat data bisnis
            $business = Business::create([
                'name' => $request->business_name,
            ]);

            // Membuat user owner
            $user = User::create([
                'business_id' => $business->id,
                'name'        => $request->name,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
            ]);

            DB::commit();

            // ❌ Auth::login($user); <-- KITA HAPUS BARIS INI (agar tidak otomatis tersimpan sesi login)

            // Redirect ke route login
            return redirect()
                ->route('login')
                ->with('success', 'Akun berhasil dibuat! Silakan login untuk melanjutkan.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}