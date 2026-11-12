<?php

namespace App\Models;

use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class Admin extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, MustVerifyEmail
{
    use InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery, Notifiable;

    //
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->is_active;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    protected static function booted(): void
    {
        static::deleting(function (Admin $admin) {
            if (! $admin->isSuperAdmin() || ! $admin->is_active) {
                return;
            }

            $activeSuperAdmins = static::query()
                ->where('is_active', true)
                ->where('role', 'super_admin')
                ->lockForUpdate()
                ->get();

            if ($activeSuperAdmins->count() <= 1) {
                throw new RuntimeException(
                    'The last active super admin cannot be deleted.',
                );
            }
        });

        static::saving(function (Admin $admin) {
            if (! $admin->exists) {
                return;
            }

            $wasActiveSuperAdmin = $admin->getOriginal('role') === 'super_admin' && (bool) $admin->getOriginal('is_active');
            $willBeActiveSuperAdmin = $admin->role === 'super_admin' && (bool) $admin->is_active;

            if (! $wasActiveSuperAdmin || $willBeActiveSuperAdmin) {
                return;
            }

            $otherActiveSuperAdmins = static::query()
                ->whereKeyNot($admin->getKey())
                ->where('role', 'super_admin')
                ->where('is_active', true)
                ->count();

            if ($otherActiveSuperAdmins === 0) {
                throw ValidationException::withMessages([
                    'role' => 'The last active super admin cannot be deactivated or demoted.',
                ]);
            }

            if (
                $admin->isDirty('role')
                && $admin->role !== 'super_admin'
                && auth('admin')->id() === $admin->getKey()
            ) {
                throw ValidationException::withMessages([
                    'role' => 'You cannot change your own super admin role.',
                ]);
            }
        });
    }
}
