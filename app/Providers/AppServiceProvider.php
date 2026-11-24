<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Application;
use App\Observers\ApplicationObserver;
use App\Policies\AdminPolicy;
use BezhanSalleh\LanguageSwitch\Enums\Placement;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAdminLocalization();

        Application::observe(ApplicationObserver::class);

        DatabaseNotification::creating(function (DatabaseNotification $notification): void {
            if ($notification->getAttribute('sort_order') === null) {
                $notification->forceFill([
                    'sort_order' => (int) DatabaseNotification::query()->max('sort_order') + 1,
                ]);
            }
        });

        Gate::policy(Admin::class, AdminPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureAdminLocalization(): void
    {
        LanguageSwitch::configureUsing(
            function (LanguageSwitch $switch): void {
                $switch
                    ->locales(config('app.supported_locales', ['en', 'ar']))
                    ->nativeLabel()
                    ->visible(outsidePanels: true)
                    ->outsidePanelPlacement(Placement::TopEnd)
                    ->outsidePanelRoutes([
                        'auth.login',
                        'auth.password-reset.request',
                        'auth.password-reset.reset',
                        'auth.email-verification.prompt',
                        'auth.multi-factor-authentication.set-up-required',
                    ])
                    ->cookieName('jobnest_admin_locale')
                    ->userPreferredLocale(function (): ?string {
                        $locale = request()->cookie('jobnest_admin_locale');

                        return is_string($locale) ? $locale : null;
                    });
            }
        );

        Event::listen(function (LocaleUpdated $event): void {
            Carbon::setLocale($event->locale);
        });
    }
}
