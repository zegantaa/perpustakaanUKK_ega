<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Anggota;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'nis' => ['required', 'string', 'max:30', 'unique:anggotas,nis'],
            'kelas' => ['required', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'nama_lengkap' => $data['nama_lengkap'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'anggota',
                'status' => 'aktif',
            ]);

            Anggota::create([
                'id_user' => $user->id_user,
                'nis' => $data['nis'],
                'nama_lengkap' => $data['nama_lengkap'],
                'kelas' => $data['kelas'],
                'alamat' => $data['alamat'] ?? null,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $tujuan = $user->role === 'anggota' ? route('user.dashboard') : route('dashboard');
        return redirect()->to($tujuan)->with('success', 'Pendaftaran berhasil. Selamat datang di Sistem Perpustakaan.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'login_role' => ['required', 'in:staff,anggota'],
        ]);
        $loginRole = $credentials['login_role'];
        unset($credentials['login_role']);

        if ($this->authenticate($credentials, $request->boolean('remember'))) {
            $roleAllowed = $loginRole === 'anggota'
                ? auth()->user()->role === 'anggota'
                : in_array(auth()->user()->role, ['admin', 'petugas'], true);

            if (! $roleAllowed) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $roleLabel = $loginRole === 'anggota' ? 'Anggota' : 'Admin / Petugas';
                return back()
                    ->withErrors(['login_role' => "Akun tersebut tidak memiliki akses sebagai {$roleLabel}."])
                    ->withInput($request->only('username', 'login_role'));
            }

            $request->session()->regenerate();

            $isAnggota = auth()->user()->role === 'anggota';
            $tujuan = $isAnggota ? route('user.dashboard') : route('dashboard');
            $pesan = $isAnggota
                ? 'Selamat datang di Sistem Perpustakaan.'
                : 'egaaa';

            return redirect()->intended($tujuan)->with('success', $pesan);
        }

        return back()->withErrors(['username' => 'Username atau password tidak sesuai.'])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function authenticate(array $credentials, bool $remember): bool
    {
        $user = User::where('username', $credentials['username'])
            ->where('status', 'aktif')
            ->first();

        if (! $user) {
            return false;
        }

        $password = (string) $credentials['password'];
        $storedPassword = (string) $user->password;

        if (Hash::isHashed($storedPassword)) {
            if (! Hash::check($password, $storedPassword)) {
                return false;
            }
        } else {
            if (! hash_equals($storedPassword, $password)) {
                return false;
            }

            $user->password = $password;
            $user->save();
        }

        Auth::login($user, $remember);

        return true;
    }
}