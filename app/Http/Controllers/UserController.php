<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->string('cari')->trim()->toString();
        $users = User::when($cari, fn ($query) => $query->where('username', 'like', "%{$cari}%")
            ->orWhere('nama_lengkap', 'like', "%{$cari}%")
            ->orWhere('email', 'like', "%{$cari}%"))->latest()->paginate(10)->appends($request->query());
        return view('user.index', compact('users', 'cari'));
    }

    public function create() { return view('user.create'); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('user.index')->with('success', 'Data user berhasil ditambahkan.');
    }

    public function show(User $user) { return view('user.show', compact('user')); }

    public function edit(User $user) { return view('user.edit', compact('user')); }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        if (blank($data['password'] ?? null)) unset($data['password']);
        if (isset($data['password'])) $data['password'] = Hash::make($data['password']);
        $user->update($data);
        return redirect()->route('user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id_user === auth()->id()) return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        if ($user->anggota || $user->transaksi()->exists()) return back()->with('error', 'User tidak dapat dihapus karena masih terhubung dengan data lain.');
        $user->delete();
        return redirect()->route('user.index')->with('success', 'Data user berhasil dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $username = 'unique:users,username' . ($user ? ',' . $user->id_user . ',id_user' : '');
        $email = 'unique:users,email' . ($user ? ',' . $user->id_user . ',id_user' : '');
        return $request->validate([
            'username' => ['required', 'string', 'max:50', $username],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', $email],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,petugas,anggota'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);
    }
}