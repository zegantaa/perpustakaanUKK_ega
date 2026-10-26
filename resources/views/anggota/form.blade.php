@csrf
<label>User Akun
	<input type="text" name="username" value="{{ old('username', $anggota->user->username ?? '') }}" placeholder="Masukkan username akun" required>
</label>
<label>Nama Lengkap
	<input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap ?? '') }}" placeholder="Masukkan nama lengkap" required>
</label>
<div class="form-grid">
	<label>NIS
		<input type="text" name="nis" value="{{ old('nis', $anggota->nis ?? '') }}" placeholder="Masukkan NIS" required>
	</label>
	<label>Kelas
		<input type="text" name="kelas" value="{{ old('kelas', $anggota->kelas ?? '') }}" placeholder="Contoh: XII RPL 1" required>
	</label>
</div>
<label>Password
	<input type="password" name="password" placeholder="Masukkan password" {{ isset($anggota) ? '' : 'required' }}>
</label>
<label>Alamat
	<textarea name="alamat" rows="4" placeholder="Masukkan alamat">{{ old('alamat', $anggota->alamat ?? '') }}</textarea>
</label>
@include('partials.form-actions', ['cancelUrl' => route('anggota.index')])