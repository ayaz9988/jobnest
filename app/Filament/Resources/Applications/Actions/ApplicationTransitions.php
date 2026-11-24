<?php

namespace App\Filament\Resources\Applications\Actions;

use App\Actions\Applications\ApplicationTransitionException;
use App\Actions\Applications\TransitionApplication;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class ApplicationTransitions
{
    /**
     * @return array<int, Action>
     */
    public static function make(): array
    {
        return [
            static::action(
                name: 'markReviewed',
                label: __('admin.actions.mark_reviewed'),
                to: 'reviewed',
                icon: Heroicon::OutlinedCheck,
            ),
            static::action(
                name: 'scheduleInterview',
                label: __('admin.actions.schedule_interview'),
                to: 'interview',
                icon: Heroicon::OutlinedCalendar,
            ),
            static::action(
                name: 'markHired',
                label: __('admin.actions.mark_hired'),
                to: 'hired',
                icon: Heroicon::OutlinedCheckBadge,
            ),
            static::action(
                name: 'reject',
                label: __('admin.actions.reject'),
                to: 'rejected',
                icon: Heroicon::OutlinedXMark,
                color: 'danger',
            ),
        ];
    }

    protected static function action(
        string $name,
        string $label,
        string $to,
        Heroicon $icon,
        string $color = 'gray',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->visible(
                fn (Application $record): bool => app(TransitionApplication::class)
                    ->canTransition($record, $to),
            )
            ->authorize('update')
            ->action(function (Application $record, Component $livewire) use ($to): void {
                try {
                    app(TransitionApplication::class)->handle($record, $to);
                } catch (ApplicationTransitionException $exception) {
                    if ($livewire instanceof ListRecords) {
                        $livewire->flushCachedTableRecords();
                    }

                    Notification::make()
                        ->danger()
                        ->title(__('admin.notifications.application_not_updated'))
                        ->body($exception->getMessage())
                        ->send();

                    return;
                }

                if ($livewire instanceof ViewRecord) {
                    $livewire->getRecord()->refresh();
                    $livewire->refreshFormData(['status']);
                } elseif ($livewire instanceof ListRecords) {
                    $livewire->flushCachedTableRecords();
                }

                Notification::make()
                    ->success()
                    ->title(__('admin.notifications.application_status_updated'))
                    ->send();
            });
    }
}
