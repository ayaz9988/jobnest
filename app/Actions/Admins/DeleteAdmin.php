<?php

namespace App\Actions\Admins;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeleteAdmin
{
    public function handle(Admin $actor, Admin $target): bool
    {
        return DB::transaction(function () use ($actor, $target): bool {
            $activeSuperAdmins = Admin::query()
                ->where('is_active', true)
                ->where('role', 'super_admin')
                ->lockForUpdate()
                ->get();

            $target = Admin::query()
                ->whereKey($target->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($actor->is($target)) {
                throw new RuntimeException(__('admin.errors.cannot_delete_self'));
            }

            if (
                $target->isSuperAdmin()
                && $target->is_active
                && $activeSuperAdmins->count() <= 1
            ) {
                throw new RuntimeException(
                    __('admin.errors.last_super_admin_delete')
                );
            }

            return (bool) $target->delete();
        }, 5);
    }
}
