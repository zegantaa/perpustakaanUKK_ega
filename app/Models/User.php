<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'nama_lengkap',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $primaryKey = 'id_user';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Pastikan password selalu tersimpan sebagai hash.
     *
     * Nilai yang sudah berupa hash (mis. hasil Hash::make()) dibiarkan apa adanya,
     * sedangkan teks biasa otomatis di-hash. Ini mencegah tersimpannya password
     * polos ke database yang menyebabkan error saat proses login.
     */
    public function setPasswordAttribute($value): void
    {
        if (is_string($value) && $value !== '' && ! Hash::isHashed($value)) {
            $value = Hash::make($value);
        }

        $this->attributes['password'] = $value;
    }

    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'id_user', 'id_user');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'user_id', 'id_user');
    }
}
