<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Application;

class ApplicationPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active;
    }

    public function view(Admin $admin, Application $target): bool
    {
        return $admin->is_active;
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active;
    }

    public function update(Admin $admin, Application $target): bool
    {
        return $admin->is_active;
    }

    public function delete(Admin $admin, Application $target): bool
    {
        return $this->isSuperAdmin($admin);
    }

    public function deleteAny(Admin $admin): bool
    {
        return $this->isSuperAdmin($admin);
    }

    private function isSuperAdmin(Admin $admin): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }
}
