<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->string('cari')->trim()->toString();
        $anggotas = Anggota::with('user')->when($cari, fn ($query) => $query->where('nis', 'like', "%{$cari}%")
            ->orWhere('nama_lengkap', 'like', "%{$cari}%")
            ->orWhere('kelas', 'like', "%{$cari}%"))->latest()->paginate(10)->withQueryString();
        return view('anggota.index', compact('anggotas', 'cari'));
    }

    public function editMenu(Request $request)
    {
        $cari = $request->string('cari')->trim()->toString();
        $anggotas = Anggota::with('user')->when($cari, fn ($query) => $query->where('nis', 'like', "%{$cari}%")
            ->orWhere('nama_lengkap', 'like', "%{$cari}%")
            ->orWhere('kelas', 'like', "%{$cari}%"))->latest()->paginate(10)->withQueryString();

        return view('anggota.edit-menu', compact('anggotas', 'cari'));
    }

    public function create()
    {
        return view('anggota.create');
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $data = $this->validated($request);
                $data['id_user'] = $this->resolveUser($data['username'], $data['nama_lengkap'], $data['password'])->id_user;
                unset($data['username']);
                unset($data['password']);
                Anggota::create($data);
            });
        } catch (QueryException $exception) {
            report($exception);
            return back()->withInput()->with('error', 'Data anggota gagal disimpan karena kendala database. Periksa username atau NIS yang digunakan.');
        }

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil ditambahkan.');
    }

    public function show(Anggota $anggota)
    {
        $anggota->load(['user', 'transaksi.buku']);
        return view('anggota.show', compact('anggota'));
    }

    public function edit(Anggota $anggota)
    {
        return view('anggota.edit', compact('anggota'));
    }

    public function update(Request $request, Anggota $anggota)
    {
        try {
            DB::transaction(function () use ($request, $anggota) {
                $data = $this->validated($request, $anggota);
                $data['id_user'] = $this->resolveUser($data['username'], $data['nama_lengkap'], $data['password'] ?? null, $anggota)->id_user;
                unset($data['username']);
                unset($data['password']);
                $anggota->update($data);
            });
        } catch (QueryException $exception) {
            report($exception);
            return back()->withInput()->with('error', 'Data anggota gagal diperbarui karena kendala database. Periksa username atau NIS yang digunakan.');
        }

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota)
    {
        if ($anggota->transaksi()->exists()) {
            return back()->with('error', 'Anggota tidak dapat dihapus karena sudah memiliki transaksi.');
        }

        try {
            $anggota->delete();
        } catch (QueryException $exception) {
            report($exception);
            return back()->with('error', 'Anggota tidak dapat dihapus karena masih memiliki data terkait.');
        }

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil dihapus.');
    }

    private function validated(Request $request, ?Anggota $anggota = null): array
    {
        $nisRule = 'unique:anggotas,nis' . ($anggota ? ',' . $anggota->id_anggota . ',id_anggota' : '');
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
            'password' => [$anggota ? 'nullable' : 'required', 'string', 'min:6'],
            'nis' => ['required', 'string', 'max:30', $nisRule],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'kelas' => ['required', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
        ]);

        return $data;
    }

    private function resolveUser(string $username, string $namaLengkap, ?string $password, ?Anggota $anggota = null): User
    {
        $user = User::where('username', $username)->first();

        if (! $user) {
            return User::create([
                'username' => $username,
                'nama_lengkap' => $namaLengkap,
                'email' => 'anggota-' . Str::uuid() . '@local.invalid',
                'password' => Hash::make($password),
                'role' => 'anggota',
                'status' => 'aktif',
            ]);
        }

        if ($user->anggota && (! $anggota || $user->anggota->id_anggota !== $anggota->id_anggota)) {
            throw ValidationException::withMessages([
                'username' => 'Username akun tersebut sudah terhubung ke anggota lain.',
            ]);
        }

        if ($password !== null && $password !== '') {
            $user->update(['password' => Hash::make($password)]);
        }

        return $user;
    }
}