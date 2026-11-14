<?php

namespace App\Actions\Applications;

use App\Models\Application;

class WithdrawApplication
{
    /**
     * Withdraw an application if its current status still allows it.
     *
     * Returns false when the application can no longer be withdrawn
     * (already withdrawn, hired, or rejected).
     */
    public function handle(Application $application): bool
    {
        if (! $application->canBeWithdrawn()) {
            return false;
        }

        return $application->update([
            'status' => Application::STATUS_WITHDRAWN,
        ]);
    }
}
