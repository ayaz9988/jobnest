<?php

namespace App\Observers;

use App\Models\Application;
use App\Notifications\ApplicationHired;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\ApplicationSubmitted;

class ApplicationObserver
{
    public function created(Application $application): void
    {
        $application->statusHistories()->create([
            'from_status' => null,
            'to_status' => $application->status,
        ]);

        if ($application->status === Application::STATUS_APPLIED) {
            $this->notifySubmitted($application);
        }
    }

    public function updated(Application $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }

        $application->statusHistories()->create([
            'from_status' => $application->getOriginal('status'),
            'to_status' => $application->status,
        ]);

        if ($application->status === Application::STATUS_APPLIED) {
            $this->notifySubmitted($application);

            return;
        }

        if ($application->status === Application::STATUS_WITHDRAWN) {
            return;
        }

        if ($application->status === Application::STATUS_HIRED) {
            $this->notifyHired($application);

            return;
        }

        $this->notifyStatusChanged($application);
    }

    private function notifyHired(Application $application): void
    {
        $application->loadMissing('jobListing');

        $application->user?->notify(new ApplicationHired($application));
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
