<?php

namespace App\Observers;

use App\Models\Application;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\ApplicationSubmitted;

class ApplicationObserver
{
    public function created(Application $application): void
    {
        if ($application->status === Application::STATUS_APPLIED) {
            $this->notifySubmitted($application);
        }
    }

    public function updated(Application $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }

        if ($application->status === Application::STATUS_APPLIED) {
            $this->notifySubmitted($application);

            return;
        }

        if ($application->status === Application::STATUS_WITHDRAWN) {
            return;
        }

        $this->notifyStatusChanged($application);
    }

    private function notifySubmitted(Application $application): void
    {
        $application->loadMissing('jobListing');

        $application->user?->notify(new ApplicationSubmitted($application));
    }

    private function notifyStatusChanged(Application $application): void
    {
        $application->loadMissing('jobListing');

        $application->user?->notify(new ApplicationStatusChanged($application));
    }
}
