<?php

namespace App\Models;

use App\Notifications\DefinirMotDePasse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Mail de réinitialisation en français (remplace celui de Laravel). */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new DefinirMotDePasse($token));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCommercant(): bool
    {
        return $this->role === 'commercant';
    }

    public function isBenevole(): bool
    {
        return $this->role === 'benevole';
    }
}
