<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Resume;

class ResumePolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active;
    }

    public function view(Admin $admin, Resume $target): bool
    {
        return $admin->is_active;
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active;
    }

    public function update(Admin $admin, Resume $target): bool
    {
        return $admin->is_active;
    }

    public function delete(Admin $admin, Resume $target): bool
    {
        return $this->isSuperAdmin($admin);
    }

    public function deleteAny(Admin $admin): bool
    {
        return $this->isSuperAdmin($admin);
    }

    public function download(Admin $admin, Resume $target): bool
    {
        return $admin->is_active;
    }

    private function isSuperAdmin(Admin $admin): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }
}
