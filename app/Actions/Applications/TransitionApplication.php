<?php

namespace App\Actions\Applications;

use App\Models\Application;
use App\Support\AdminTranslations;
use Illuminate\Support\Facades\DB;

class TransitionApplication
{
    public const TRANSITIONS = [
        'applied' => ['reviewed', 'rejected'],
        'reviewed' => ['interview', 'rejected'],
        'interview' => ['hired', 'rejected'],
        'withdraw' => [],
        'hired' => [],
        'rejected' => [],
    ];

    public function handle(Application $application, string $to): Application
    {
        return DB::transaction(function () use ($application, $to): Application {
            $application = Application::query()
                ->whereKey($application->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->canTransition($application, $to)) {
                throw new ApplicationTransitionException(
                    __('admin.errors.invalid_application_transition', [
                        'from' => AdminTranslations::option('application_statuses', $application->status),
                        'to' => AdminTranslations::option('application_statuses', $to),
                    ])
                );
            }

            $application->update(['status' => $to]);

            return $application;
        }, 5);
    }

    public function canTransition(Application $application, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$application->status], true);
    }
}
