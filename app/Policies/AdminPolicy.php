<?php

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $this->isSuperAdmin($admin);
    }

    public function view(Admin $admin, Admin $target): bool
    {
        return $this->isSuperAdmin($admin);
    }

    public function create(Admin $admin): bool
    {
        return $this->isSuperAdmin($admin);
    }

    public function update(Admin $admin, Admin $target): bool
    {
        return $this->isSuperAdmin($admin);
    }

    public function delete(Admin $admin, Admin $target): bool
    {
        if (! $this->isSuperAdmin($admin) || $admin->is($target)) {
            return false;
        }

        if ($target->is_active && $target->isSuperAdmin() && $this->activeSuperAdminCount() <= 1) {
            return false;
        }

        return true;
    }

    public function deleteAny(Admin $admin): bool
    {
        return $this->isSuperAdmin($admin);
    }

    private function isSuperAdmin(Admin $admin): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }

    private function activeSuperAdminCount(): int
    {
        return Admin::where('is_active', true)
            ->where('role', 'super_admin')
            ->count();
    }
}
