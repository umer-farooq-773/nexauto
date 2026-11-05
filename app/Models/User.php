<?php

namespace App\Models;

use App\Relationships\FileRelationship;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, FileRelationship;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
        'provider_id',
        'provider',
        'email_verified_at'
    ];

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->first_name = Str::title($user->first_name ?? '');
            $user->last_name = Str::title($user->last_name ?? '');
        });

        static::updating(function ($user) {
            $user->first_name = Str::title($user->first_name ?? '');
            $user->last_name = Str::title($user->last_name ?? '');
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->attributes['first_name'] ?? '') . ' ' . ($this->attributes['last_name'] ?? ''));
    }

    public function getRoleDisplayNameAttribute(): string
    {
        $role = $this->relationLoaded('roles')
            ? $this->roles->first()?->name
            : $this->roles()->first()?->name;

        if (!$role) {
            return 'User';
        }

        return match ($role) {
            \App\Constants\Constants::SUPERADMIN => 'Super Admin',
            \App\Constants\Constants::ADMIN => 'Admin',
            \App\Constants\Constants::CHAMPS_MANAGEMENT => 'Champs Management',
            \App\Constants\Constants::CLIENT_DIRECTORS => 'Client Director',
            \App\Constants\Constants::CLIENT_MANAGEMENT => 'Client Management',
            \App\Constants\Constants::FIELD_WORKER => 'Field Worker',
            \App\Constants\Constants::SECURITY_OPERATIVE => 'Security Operative',
            default => ucwords(str_replace('_', ' ', $role)),
        };
    }

    public function profile()
    {
        return $this->fileUrl('profile');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }
}
