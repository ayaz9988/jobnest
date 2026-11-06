# Working with Admins & the Admin Dashboard

This guide covers the separate `admins` table, admin authentication, and the Filament admin
dashboard.

## Quick facts

| Item | Value |
|---|---|
| Table | `admins` |
| Model | `App\Models\Admin` — created |
| Guard | `admin` |
| Provider | `admins` |
| Password reset table | `admin_password_reset_tokens` |
| Admin roles | `admin`, `super_admin` |
| Dashboard package | Filament v5 — installed and configured |
| Dashboard URL | `/admin` |

## Current status

Already implemented:

- `admins` migration with role, active flag, Filament MFA columns, and login tracking
- `App\Models\Admin` with `FilamentUser` and MFA interfaces
- `admin` guard, `admins` provider, and `admins` password broker in `config/auth.php`
- `AdminPanelProvider` registered in `bootstrap/providers.php`
- Filament v5 installed
- Admin panel configured with the `admin` guard, password reset, profile page, and required MFA
- First `super_admin` created

Still to do:

- Build Filament resources and dashboard widgets
- Add tests for admin access and MFA

Admins are a **separate identity** from normal users:

```text
/login, /two-factor-challenge   → Fortify → web guard   → users
/admin/login                    → Filament → admin guard → admins
```

Never put admin accounts in the `users` table.

## Columns

The `admins` migration is `2026_11_03_133028_add_admins_table.php`.

| Column | Notes |
|---|---|
| `id` | Primary key |
| `name` | Display name |
| `email` | Unique login identifier |
| `email_verified_at` | Nullable timestamp |
| `password` | Stored hashed by the model cast |
| `role` | `admin` or `super_admin`, default `admin` |
| `is_active` | Disabled admins must not access the panel |
| `app_authentication_secret` | Filament TOTP secret, encrypted |
| `app_authentication_recovery_codes` | Filament recovery codes, encrypted array |
| `last_login_at` | Nullable login timestamp |
| `last_login_ip` | Nullable IP address |
| `remember_token` | Laravel “remember me” |
| `created_at`, `updated_at` | Timestamps |

## Rules

1. **Admins are not users.** Do not add an `admin` value to `users.role`.
2. **No public admin registration.** Create admins manually or through a seeder/command.
3. **Never use `php artisan make:filament-user`** in this project — it creates a normal `User`.
4. **Only super admins can manage admins.** Check `isSuperAdmin()` in a policy or Filament resource.
5. **Inactive admins must be blocked.** Enforce `is_active` in `canAccessPanel()`.
6. **Do not edit old migrations.** Add a new migration for schema changes.
7. **Use the `admin` guard on admin routes.** Do not use `auth()->user()` inside `/admin`.

## 1. Admin guard (already configured)

This configuration is already present in `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => env('AUTH_MODEL', App\Models\User::class),
    ],

    'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\Admin::class,
    ],
],

'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
        'expire' => 60,
        'throttle' => 60,
    ],

    'admins' => [
        'provider' => 'admins',
        'table' => 'admin_password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
],
```

Keep the default guard as `web`:

```php
'defaults' => [
    'guard' => 'web',
    'passwords' => 'users',
],
```

## 2. Admin model (already created)

The model lives at `app/Models/Admin.php`:

```php
<?php

namespace App\Models;

use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    use HasFactory;
    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->is_active;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}
```

The two Filament traits use the existing `app_authentication_*` columns. No extra migration is
needed for admin MFA.

## 3. Create admins

The first `super_admin` already exists. To create another admin:

```bash
php artisan tinker
```

```php
App\Models\Admin::create([
    'name' => 'Super Admin',
    'email' => 'admin@jobnest.test',
    'password' => 'change-me-now',
    'role' => 'super_admin',
    'is_active' => true,
]);
```

The `password` cast hashes the value automatically.

## 4. Filament installation (already installed)

Filament v5 is already installed and `AdminPanelProvider` is registered in
`bootstrap/providers.php`. No installation step is needed.

Verify the package:

```bash
composer show filament/filament | head -n 1
```

Do not rerun `php artisan filament:install --panels`; the panel already exists.

## 5. Admin panel configuration (already configured)

The panel is configured in `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Filament\Auth\MultiFactor\App\AppAuthentication;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->profile()
        ->passwordReset()
        ->brandName('JobNest')
        ->authGuard('admin')
        ->authPasswordBroker('admins')
        ->multiFactorAuthentication([
            AppAuthentication::make()
                ->recoverable(),
        ], isRequired: true)
        // ...colors, resources, pages, widgets, middleware
        ;
}
```

Important options:

| Option | Why |
|---|---|
| `->authGuard('admin')` | Use the `admins` table |
| `->authPasswordBroker('admins')` | Use `admin_password_reset_tokens` |
| `->login()` | Admin login page |
| `->profile()` | Where admins set up MFA |
| `->passwordReset()` | Admin password resets |
| `->multiFactorAuthentication(..., isRequired: true)` | Force TOTP for admins |

Do **not** add `->registration()` — admins must not self-register.

## 6. Verify admin login

```bash
php artisan migrate
php artisan optimize:clear
composer dev
```

Open:

```text
http://localhost:8000/admin
```

Expected flow:

1. Admin logs in with email/password.
2. Filament prompts MFA setup.
3. Admin scans the QR code.
4. Admin reaches the dashboard.

Normal users must not be able to access `/admin`.

## 7. Working with the admins table

### Add a column

```bash
php artisan make:migration add_column_to_admins_table --table=admins
```

```php
public function up(): void
{
    Schema::table('admins', function (Blueprint $table) {
        $table->string('job_title')->nullable()->after('name');
    });
}

public function down(): void
{
    Schema::table('admins', function (Blueprint $table) {
        $table->dropColumn('job_title');
    });
}
```

Then:

1. Add the column to `$fillable` in `Admin`.
2. Add a cast if needed.
3. Add it to `AdminFactory`.
4. Run `php artisan migrate` and `php artisan test`.

### Query admins

```php
$admins = Admin::where('role', 'admin')->get();
$superAdmins = Admin::where('role', 'super_admin')->get();

$admin = Admin::where('email', 'admin@jobnest.test')->firstOrFail();
```

### Check roles

```php
if (auth('admin')->user()->isSuperAdmin()) {
    // super-admin-only action
}
```

Do not rely on the guard alone for authorization. Use policies or resource `can*()` methods.

## 8. Creating Filament resources

### Do resources need controllers?

No. A Filament resource is a Livewire UI. Normal admin CRUD is handled by the resource pages, so you do **not** create a controller for it.

Create a controller only when you need something outside the resource UI, such as:

- A JSON API
- A webhook
- A file export
- A custom action that is not a Filament page

For normal CRUD, create a resource and edit its form/table classes.

### Generate a resource

The domain models must exist first. Currently `app/Models` contains only `Admin` and `User`.

For an existing model:

```bash
php artisan make:filament-resource User --generate
php artisan make:filament-resource Admin --generate
```

For a model that does not exist yet, let Filament create the model too:

```bash
php artisan make:filament-resource JobListing --model --generate
php artisan make:filament-resource Category --model --generate
php artisan make:filament-resource Skill --model --generate
php artisan make:filament-resource Application --model --generate
php artisan make:filament-resource Resume --model --generate
```

`--generate` reads the database columns and fills in the form and table automatically. Omit
`--model` once the model exists.

The panel discovers resources automatically from:

```text
app/Filament/Resources
```

### Generated structure

A resource generated for `JobListing` looks like this:

```text
app/Filament/Resources/JobListings/
├── JobListingResource.php
├── Pages/
│   ├── CreateJobListing.php
│   ├── EditJobListing.php
│   └── ListJobListings.php
├── Schemas/
│   └── JobListingForm.php
└── Tables/
    └── JobListingsTable.php
```

Edit the form in `Schemas/JobListingForm.php`:

```php
<?php

namespace App\Filament\Resources\JobListings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JobListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255),

            Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),

            Select::make('employment_type')
                ->options([
                    'full_time' => 'Full time',
                    'part_time' => 'Part time',
                    'contract' => 'Contract',
                    'internship' => 'Internship',
                    'temporary' => 'Temporary',
                ])
                ->required(),

            Select::make('workplace_type')
                ->options([
                    'on_site' => 'On site',
                    'remote' => 'Remote',
                    'hybrid' => 'Hybrid',
                ])
                ->required(),

            Textarea::make('description')
                ->required()
                ->columnSpanFull(),

            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'open' => 'Open',
                    'closed' => 'Closed',
                    'filled' => 'Filled',
                ])
                ->required(),
        ]);
    }
}
```

This example assumes `JobListing` has a `category()` relationship. If it does not exist yet, add
it to the model before using `relationship('category', 'name')`.

Edit the table in `Tables/JobListingsTable.php`:

```php
<?php

namespace App\Filament\Resources\JobListings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employment_type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('workplace_type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'filled' => 'Filled',
                    ]),
            ]);
    }
}
```

Then open:

```text
/admin/job-listings
```

If the page does not appear, clear the cached views and routes:

```bash
php artisan optimize:clear
```

---

## 9. Creating controllers for custom admin endpoints

Use a controller when the feature must live outside Filament.

Generate one:

```bash
# Full resource controller
php artisan make:controller Admin/JobListingController --resource

# JSON-only controller (no create/edit views)
php artisan make:controller Admin/JobListingController --api
```

The example below implements only `index` and `show`; add the remaining methods you need.

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;

class JobListingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            JobListing::query()->latest()->paginate(20)
        );
    }

    public function show(JobListing $jobListing): JsonResponse
    {
        return response()->json($jobListing);
    }
}
```

Do not put authentication logic in the controller. Put it on the route.

### Custom admin routes

Filament already owns `/admin`. Avoid colliding with its routes. Use a separate prefix such
as `/admin-api` or `/admin-tools` for custom controllers:

```php
use App\Http\Controllers\Admin\JobListingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin', 'admin.role'])
    ->prefix('admin-api')
    ->name('admin.api.')
    ->group(function () {
        Route::get('/job-listings', [JobListingController::class, 'index'])
            ->name('job-listings.index');

        Route::get('/job-listings/{jobListing}', [JobListingController::class, 'show'])
            ->name('job-listings.show');
    });
```

Super-admin-only endpoints:

```php
Route::middleware(['auth:admin', 'admin.role:super_admin'])
    ->prefix('admin-api/admins')
    ->name('admin.api.admins.')
    ->group(function () {
        // Only super admins reach this group.
    });
```

If you need an admin UI page rather than a controller, use a Filament page:

```bash
php artisan make:filament-page ManageReports
```

---

## 10. Putting auth guards on routes

A guard answers **which identity table** is being used. A role check answers **what that identity
is allowed to do**.

| URL | Guard | Middleware |
|---|---|---|
| `/dashboard` | `web` | `auth` |
| `/employer/*` | `web` | `auth`, `user.role:employer` |
| `/admin` | `admin` | Filament's auth middleware |
| `/admin-api/*` | `admin` | `auth:admin`, `admin.role` |
| `/admin-api/admins/*` | `admin` | `auth:admin`, `admin.role:super_admin` |

### Normal user routes

`auth` uses the default `web` guard:

```php
Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});
```

Be explicit when the distinction matters:

```php
Route::middleware(['auth:web'])->group(function () {
    // Only users from the users table.
});
```

Employer-only user routes:

```php
Route::middleware(['auth', 'user.role:employer'])
    ->prefix('employer')
    ->name('employer.')
    ->group(function () {
        // Employer-only routes.
    });
```

### Admin routes

Use `auth:admin` for custom admin controllers:

```php
Route::middleware(['auth:admin'])->prefix('admin-api')->group(function () {
    // Only authenticated admins.
});
```

Add `admin.role` to also enforce `is_active` and role checks:

```php
Route::middleware(['auth:admin', 'admin.role'])
    ->prefix('admin-api')
    ->group(function () {
        // Active admins only.
    });
```

Important:

- `auth:admin` does **not** automatically enforce `is_active` on custom routes.
- `canAccessPanel()` protects the Filament panel, not custom controllers.
- Use `auth:admin` plus the active/role middleware for every custom admin endpoint.
- Do not place session-based admin routes in `routes/api.php`; that file does not use the
  session middleware by default.
- Prefer `routes/web.php` for session-authenticated admin controllers.

### Register the middleware aliases

Create the admin middleware:

```bash
php artisan make:middleware EnsureAdminRole
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->is_active) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Admin account is inactive.');
        }

        if ($roles !== [] && ! in_array($admin->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
```

Create the user role middleware:

```bash
php artisan make:middleware EnsureUserRole
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ($roles !== [] && ! in_array($user->role, $roles, true))) {
            abort(403);
        }

        return $next($request);
    }
}
```

Register both in `bootstrap/app.php`:

```php
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.role' => EnsureAdminRole::class,
            'user.role' => EnsureUserRole::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin-api/*') || $request->is('admin-tools/*')
            ? route('filament.admin.auth.login')
            : route('login'));
    })
    // ...
```

The redirect is important: without it, an unauthenticated request to a custom admin route
redirects to the normal user login page instead of `/admin/login`.

---

## 11. Role-based authorization

There are two separate role systems:

| Identity | Table | Roles |
|---|---|---|
| Normal user | `users` | `seeker`, `employer` |
| Admin | `admins` | `admin`, `super_admin` |

Do not mix them. An admin is not a user with a different role.

### Admin route roles

Use the middleware from the previous section:

```php
Route::middleware(['auth:admin', 'admin.role'])->group(function () {
    // Any active admin: admin or super_admin.
});

Route::middleware(['auth:admin', 'admin.role:super_admin'])->group(function () {
    // Super admins only.
});
```

### Admin panel resources

Filament v5 checks Laravel policies automatically. Create a policy for sensitive models:

```bash
php artisan make:policy AdminPolicy --model=Admin
```

```php
<?php

namespace App\Policies;

use App\Models\Admin;

class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    public function create(Admin $admin): bool
    {
        return $admin->isSuperAdmin();
    }

    public function update(Admin $admin, Admin $target): bool
    {
        return $admin->isSuperAdmin();
    }

    public function delete(Admin $admin, Admin $target): bool
    {
        return $admin->isSuperAdmin()
            && $admin->id !== $target->id;
    }
}
```

For `AdminResource`, this means only super admins can list, create, edit, or delete admins.
Also block deleting the last remaining super admin.

If a resource needs a rule that is not about a model policy, override a resource method:

```php
<?php

namespace App\Filament\Resources\JobListings;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

class JobListingResource extends Resource
{
    public static function canDelete(Model $record): bool
    {
        return auth('admin')->user()?->isSuperAdmin() ?? false;
    }
}
```

Recommended access:

| Resource | Admin | Super admin |
|---|---|---|
| `UserResource` | View | View, edit, delete |
| `JobListingResource` | View, edit | Full access |
| `CategoryResource` | View | Full access |
| `SkillResource` | View | Full access |
| `ApplicationResource` | View | Full access |
| `ResumeResource` | View | Full access |
| `AdminResource` | No access | Full access |

### User route roles

Use `user.role` for web users:

```php
Route::middleware(['auth', 'user.role:seeker'])
    ->prefix('seeker')
    ->name('seeker.')
    ->group(function () {
        // Seeker-only pages.
    });

Route::middleware(['auth', 'user.role:employer'])
    ->prefix('employer')
    ->name('employer.')
    ->group(function () {
        // Employer-only pages.
    });
```

### Record-level authorization for users

Use a policy when the rule depends on the record, not just the role:

```bash
php artisan make:policy JobListingPolicy --model=JobListing
```

```php
<?php

namespace App\Policies;

use App\Models\JobListing;
use App\Models\User;

class JobListingPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'employer';
    }

    public function update(User $user, JobListing $jobListing): bool
    {
        return $user->id === $jobListing->posted_by;
    }

    public function delete(User $user, JobListing $jobListing): bool
    {
        return $user->id === $jobListing->posted_by;
    }
}
```

Use it in a user controller:

```php
use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    use AuthorizesRequests;

    public function update(Request $request, JobListing $jobListing)
    {
        $this->authorize('update', $jobListing);

        // ...
    }
}
```

The base `Controller` in this project does not include `AuthorizesRequests`, so add the trait
to the controller that uses `$this->authorize()`.

If the same model is managed in both the user area and the admin panel, accept both identities:

```php
use App\Models\Admin;
use App\Models\User;

public function update(User|Admin $user, JobListing $jobListing): bool
{
    return $user instanceof Admin
        || $user->id === $jobListing->posted_by;
}
```

### Blade checks

For admin-only UI:

```blade
@if (auth('admin')->user()?->isSuperAdmin())
    <a href="{{ url('/admin-api/admins') }}">Manage admins</a>
@endif
```

For normal user roles:

```blade
@if (auth()->user()?->role === 'employer')
    <a href="{{ route('employer.dashboard') }}">Employer dashboard</a>
@endif
```

---

## 12. Dashboard widgets

Create widgets for:

```bash
php artisan make:filament-widget StatsOverview --stats-overview
php artisan make:filament-widget LatestApplications --table
php artisan make:filament-widget LatestJobListings --table
```

Useful metrics:

- Total users / seekers / employers
- Open vs closed job listings
- Applications per day
- Latest applications
- Latest job listings

## 13. Testing

If `AdminFactory` does not exist yet:

```bash
php artisan make:factory AdminFactory --model=Admin
```

Then test the admin guard explicitly:

```php
$admin = Admin::factory()->create([
    'role' => 'super_admin',
    'is_active' => true,
]);

$this->actingAs($admin, 'admin')
    ->get('/admin')
    ->assertOk();
```

Important tests:

- Active admin can log in.
- Inactive admin cannot access the panel.
- Normal web user cannot access `/admin`.
- MFA is required.
- Only super admins can manage admins.
- Last super admin cannot be deleted.

## Common mistakes

- Running `make:filament-user` and creating a normal user.
- Using `auth()->user()` instead of `auth('admin')->user()`.
- Adding `->registration()` to the admin panel.
- Forgetting `->authGuard('admin')`.
- Forgetting to check `is_active`.
- Giving every admin access to `AdminResource`.
- Editing an already-applied migration instead of adding a new one.
- Using `auth` instead of `auth:admin` on custom admin routes.
- Assuming `canAccessPanel()` protects custom admin controllers — it does not.
- Putting session-authenticated admin controllers in `routes/api.php`.
- Creating a controller for normal Filament CRUD — use a resource instead.
- Using `users.role` to represent admin access — admins belong in `admins`.
