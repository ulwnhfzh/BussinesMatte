<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman pengaturan pengguna yang sedang login.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('pengaturan.index', compact('user'));
    }

    /**
     * Memperbarui profil pengguna yang sedang login.
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validateWithBag(
            'profile',
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')
                        ->ignore($user->id),
                ],

                'profile_photo' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'remove_profile_photo' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'name.required' =>
                    'Nama lengkap wajib diisi.',

                'name.string' =>
                    'Nama lengkap harus berupa teks.',

                'name.max' =>
                    'Nama lengkap maksimal 255 karakter.',

                'email.required' =>
                    'Email wajib diisi.',

                'email.email' =>
                    'Format email tidak valid.',

                'email.max' =>
                    'Email maksimal 255 karakter.',

                'email.unique' =>
                    'Email tersebut sudah digunakan.',

                'profile_photo.image' =>
                    'File foto profil harus berupa gambar.',

                'profile_photo.mimes' =>
                    'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',

                'profile_photo.max' =>
                    'Ukuran foto profil maksimal 2 MB.',

                'remove_profile_photo.boolean' =>
                    'Perintah menghapus foto profil tidak valid.',
            ]
        );

        $oldPhotoPath = $user->profile_photo;
        $newPhotoPath = null;
        $photoChanged = false;

        /*
         * Foto baru diprioritaskan apabila pengguna mengunggah gambar,
         * meskipun pilihan hapus foto ikut terkirim.
         */
        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request
                ->file('profile_photo')
                ->store('profile-photos', 'public');

            $user->profile_photo = $newPhotoPath;
            $photoChanged = true;
        } elseif ($request->boolean('remove_profile_photo')) {
            $user->profile_photo = null;
            $photoChanged = true;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        try {
            $user->save();
        } catch (Throwable $exception) {
            /*
             * Apabila database gagal diperbarui, foto baru yang sudah
             * terunggah dihapus agar tidak menjadi file tanpa pemilik.
             */
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        /*
         * Foto lama baru dihapus setelah data profil berhasil disimpan.
         * Dengan demikian, foto lama tidak hilang jika penyimpanan gagal.
         */
        if (
            $photoChanged
            && $oldPhotoPath
            && $oldPhotoPath !== $newPhotoPath
        ) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return redirect()
            ->route('pengaturan')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Memperbarui password pengguna yang sedang login.
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validateWithBag(
            'password',
            [
                'current_password' => [
                    'required',
                    'string',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'different:current_password',
                ],
            ],
            [
                'current_password.required' =>
                    'Password lama wajib diisi.',

                'password.required' =>
                    'Password baru wajib diisi.',

                'password.min' =>
                    'Password baru minimal 8 karakter.',

                'password.confirmed' =>
                    'Konfirmasi password baru tidak sesuai.',

                'password.different' =>
                    'Password baru harus berbeda dari password lama.',
            ]
        );

        if (!Hash::check(
            $validated['current_password'],
            $user->password
        )) {
            return back()->withErrors(
                [
                    'current_password' =>
                        'Password lama tidak sesuai.',
                ],
                'password'
            );
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('pengaturan')
            ->with('success', 'Password berhasil diperbarui.');
    }
}