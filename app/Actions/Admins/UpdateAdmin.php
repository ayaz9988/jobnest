<?php

namespace App\Actions\Admins;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateAdmin
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Admin $actor, Admin $target, array $data): Admin
    {
        return DB::transaction(function () use ($actor, $target, $data): Admin {
            $activeSuperAdmins = Admin::query()
                ->where('role', 'super_admin')
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            $target = Admin::query()
                ->whereKey($target->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $newRole = $data['role'] ?? $target->role;
            $newIsActive = (bool) ($data['is_active'] ?? $target->is_active);

            $isActiveSuperAdmin = $target->isSuperAdmin() && $target->is_active;
            $willRemainActiveSuperAdmin = $newRole === 'super_admin' && $newIsActive;

            if ($isActiveSuperAdmin && ! $willRemainActiveSuperAdmin) {
                if ($activeSuperAdmins->count() <= 1) {
                    throw ValidationException::withMessages([
                        'role' => __('admin.errors.last_super_admin_demote'),
                    ]);
                }
            }

            if ($actor->is($target)) {
                if ($newRole !== $target->role) {
                    throw ValidationException::withMessages([
                        'role' => __('admin.errors.cannot_change_own_role'),
                    ]);
                }

                if (! $newIsActive) {
                    throw ValidationException::withMessages([
                        'is_active' => __('admin.errors.cannot_deactivate_self'),
                    ]);
                }
            }

            $target->update($data);

            return $target;
        }, 5);
    }
}
