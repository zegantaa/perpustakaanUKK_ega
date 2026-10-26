<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->string('cari')->trim()->toString();
        $bukus = Buku::when($cari, fn ($query) => $query->where('judul_buku', 'like', "%{$cari}%")
            ->orWhere('penulis', 'like', "%{$cari}%")
            ->orWhere('kategori', 'like', "%{$cari}%"))->latest()->paginate(10)->appends($request->query());
        return view('buku.index', compact('bukus', 'cari'));
    }

    public function editMenu(Request $request)
    {
        $cari = $request->string('cari')->trim()->toString();
        $bukus = Buku::when($cari, fn ($query) => $query->where('judul_buku', 'like', "%{$cari}%")
            ->orWhere('penulis', 'like', "%{$cari}%")
            ->orWhere('kategori', 'like', "%{$cari}%"))->latest()->paginate(10)->appends($request->query());

        return view('buku.edit-menu', compact('bukus', 'cari'));
    }

    public function create()
    {
        return view('buku.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }
        Buku::create($data);
        return redirect()->route('buku.index')->with('success', 'Data buku berhasil ditambahkan.');
    }

    public function show(Buku $buku)
    {
        return view('buku.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        return view('buku.edit', compact('buku'));
    }

    public function update(Request $request, Buku $buku)
    {
        $data = $this->validated($request);
        $coverLama = $buku->cover;
        $coverBaru = null;

        if ($request->hasFile('cover')) {
            $coverBaru = $request->file('cover')->store('covers', 'public');
            $data['cover'] = $coverBaru;
        } elseif ($request->boolean('hapus_cover')) {
            $data['cover'] = null;
        }

        $buku->update($data);

        if (($coverBaru || $request->boolean('hapus_cover')) && $coverLama && Storage::disk('public')->exists($coverLama)) {
            Storage::disk('public')->delete($coverLama);
        }
        return redirect()->route('buku.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku)
    {
        if ($buku->transaksi()->exists()) {
            return back()->with('error', 'Buku tidak dapat dihapus karena sudah memiliki transaksi.');
        }
        if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
            Storage::disk('public')->delete($buku->cover);
        }
        $buku->delete();
        return redirect()->route('buku.index')->with('success', 'Data buku berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validateWithBag('default', [
            'judul_buku' => ['required', 'string', 'max:200'],
            'penulis' => ['required', 'string', 'max:150'],
            'penerbit' => ['required', 'string', 'max:150'],
            'tahun' => ['required', 'integer', 'between:1900,2100'],
            'kategori' => ['required', 'string', 'max:100'],
            'stok' => ['required', 'integer', 'min:0'],
            'deskripsi_buku' => ['nullable', 'string'],
            'cover' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_cover' => ['nullable', 'boolean'],
        ], [
            'cover.image' => 'File cover tidak valid.',
            'cover.mimes' => 'Format cover harus JPG, JPEG, PNG, atau WEBP.',
            'cover.max' => 'Ukuran cover maksimal 2 MB.',
            'cover.file' => 'File cover tidak valid.',
        ]);
    }
}