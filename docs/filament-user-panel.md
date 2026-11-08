# Using Filament for Normal Users

This guide covers adding a separate Filament panel for normal users (seekers and employers)
while keeping the existing admin panel isolated on the `admin` guard.

## Recommended architecture

Keep two independent Filament panels:

| Panel | Guard | Path | Purpose |
|---|---|---|---|
| `admin` | `admin` | `/admin` | Admin and super-admin dashboard |
| `user` | `web` | `/app` | Seeker and employer dashboard |

Do **not** reuse the admin resources. Give the user panel its own resource, page, and widget
directories.

```text
app/Filament/
├── Resources/          # admin panel resources
└── User/
    ├── Resources/      # user panel resources
    ├── Pages/
    └── Widgets/
```

## 1. Create the user panel

```bash
php artisan make:filament-panel user
```

This creates `app/Providers/Filament/UserPanelProvider.php` and registers it in
`bootstrap/providers.php`.

Configure it roughly like this:

```php
return $panel
    ->id('user')
    ->path('app')
    ->authGuard('web')
    ->authPasswordBroker('users')
    ->brandName('JobNest')
    ->profile()
    ->discoverResources(
        in: app_path('Filament/User/Resources'),
        for: 'App\Filament\User\Resources',
    )
    ->discoverPages(
        in: app_path('Filament/User/Pages'),
        for: 'App\Filament\User\Pages',
    )
    ->discoverWidgets(
        in: app_path('Filament/User/Widgets'),
        for: 'App\Filament\User\Widgets',
    )
    ->pages([
        \Filament\Pages\Dashboard::class,
    ]);
```

Leave the admin panel as the default panel unless you want `/app` to be the default.

## 2. Make `User` implement `FilamentUser`

Filament only allows production access if the user model implements `FilamentUser`.

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, PasskeyUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'user'
            && in_array($this->role, ['seeker', 'employer'], true);
    }
}
```

This allows both seekers and employers into the user panel, while keeping them out of
`/admin`.

Without `FilamentUser`, Filament allows access only in the `local` environment. Production
requests are denied.

## 3. Decide who owns authentication

This is the main design decision.

### Option A — Keep Fortify for auth (recommended)

Fortify already handles:

- login
- registration
- password reset
- two-factor authentication
- passkeys

Use Filament only for the authenticated user area.

The user logs in through `/login`, then accesses `/app`. The user panel’s unauthenticated
redirect must point to Fortify’s login page. You can do that with a small custom Filament auth
middleware or a custom login page that redirects to `route('login')`.

This avoids losing passkey and two-factor support.

### Option B — Let Filament handle user auth

Enable on the user panel:

```php
->login()
->registration()
->passwordReset()
->emailVerification()
```

This gives you a complete Filament auth flow, but it duplicates Fortify. You would then need
to decide what to do with Fortify’s login/register routes and its 2FA/passkey flows.

Do not run two separate login systems for the same `web` guard without a clear canonical entry
point.

## 4. Avoid route conflicts

The application already has:

```php
Route::view('dashboard', 'dashboard')->name('dashboard');
```

and Fortify redirects to `/dashboard`.

If the user panel path is also `dashboard`, they will conflict. Either:

- use `/app` or `/portal` for the Filament panel, or
- remove or replace the existing dashboard route and update `config/fortify.php`:

```php
'home' => '/app',
```

## 5. Build user-specific resources

Create separate resources under `app/Filament/User/Resources/`.

### Seeker resources

- My Applications
- My Resumes
- Saved Jobs
- Browse Jobs
- Profile

### Employer resources

- My Job Listings
- Create Job Listing
- Applicants
- Company Profile

Use policies to scope every resource by ownership:

```php
class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->user_id
            || $user->id === $application->jobListing->posted_by;
    }

    public function update(User $user, Application $application): bool
    {
        return $user->id === $application->user_id;
    }
}
```

Without policies, a seeker could potentially see another seeker’s applications or resumes.

## 6. Hide resources by role

Use policy methods such as `viewAny()` to hide employer-only resources from seekers and vice
versa:

```php
public function viewAny(User $user): bool
{
    return $user->role === 'employer';
}
```

Filament hides the navigation item when `viewAny()` returns `false`.

## 7. Customize the look

Filament panels are admin-style by default. You can make the user panel feel more like a
product:

```php
->brandName('JobNest')
->colors([
    'primary' => Color::Blue,
])
->viteTheme('resources/css/filament/user/theme.css')
->sidebarCollapsibleOnDesktop()
```

For public-facing pages such as the landing page, job board, and job details, keep
Blade/Livewire/Flux. Use Filament for the authenticated dashboard and CRUD screens.

## 8. Security rules

- Keep the `admin` and `user` panels separate.
- Use `auth('web')` inside the user panel; use `auth('admin')` inside `/admin`.
- Never reuse admin resources for normal users.
- Scope every user resource with a policy.
- Add tests proving one user cannot access another user’s records.
- Implement `FilamentUser` on `User`; do not rely on the local-environment fallback.
- Enable `->strictAuthorization()` only after every user resource has a policy.

## Recommended rollout

1. Add the `user` panel with only a dashboard and profile page.
2. Make `User` implement `FilamentUser`.
3. Decide whether Fortify or Filament owns user authentication.
4. Add seeker resources and policies.
5. Add employer resources and policies.
6. Add widgets and theme customization.
7. Add tests for role-based access and record ownership.

The biggest decision is whether Filament should replace Fortify for user login and
registration. To keep passkeys and the existing 2FA setup, keep Fortify and use Filament only
for the authenticated `/app` area.
