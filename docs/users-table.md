# Working with the `users` Table

This is a quick guide for anyone changing the user side of JobNest.

## Quick facts

| Item | Value |
|---|---|
| Table | `users` |
| Model | `App\Models\User` |
| Auth system | Laravel Fortify on the `web` guard |
| Admin identity | **Separate `admins` table — not `users`** |
| User roles | `seeker`, `employer` |

The `users` table is created by `0001_01_01_000000_create_users_table.php` and extended by:

- `2025_08_14_170933_add_two_factor_columns_to_users_table.php`
- `2026_11_03_132556_add_role_profile_location_to_users.php`

## Columns

| Column | Notes |
|---|---|
| `id` | Primary key |
| `name` | Display name |
| `email` | Unique login identifier |
| `email_verified_at` | Nullable timestamp |
| `password` | Stored hashed by the `password` cast |
| `two_factor_secret` | Fortify TOTP secret, nullable |
| `two_factor_recovery_codes` | Fortify recovery codes, nullable |
| `two_factor_confirmed_at` | Nullable timestamp |
| `remember_token` | Laravel “remember me” |
| `role` | `seeker` or `employer`, default `seeker` |
| `profile` | Nullable bio/about text |
| `location` | Nullable location |
| `created_at`, `updated_at` | Timestamps |

## Important rules

1. **Never add admins to `users`.** Admin accounts live in the separate `admins` table and use the `admin` guard. Do not add an `admin` value to `users.role`.
2. **Never store plaintext passwords.** Assign the plaintext value; the model’s `password => hashed` cast stores the hash.
3. **Do not edit old migrations after they have run.** Create a new migration.
4. **Keep `email` unique.** Login and password reset depend on it.
5. **Use `role` only for `seeker` / `employer`.**

## Create a user

```php
use App\Models\User;

$user = User::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => 'secret-password', // hashed automatically
    'role' => 'seeker',
    'profile' => 'Backend developer',
    'location' => 'Dhaka',
]);
```

Or use the factory:

```bash
php artisan tinker
```

```php
User::factory()->create([
    'role' => 'employer',
]);
```

## Query users by role

```php
$seekers = User::where('role', 'seeker')->get();
$employers = User::where('role', 'employer')->get();

$user = User::where('email', 'jane@example.com')->firstOrFail();
```

## Add a new column to `users`

Example: adding a `phone` column.

### 1. Create a migration

```bash
php artisan make:migration add_phone_to_users_table --table=users
```

### 2. Edit the new migration

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone', 20)->nullable()->after('location');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('phone');
    });
}
```

### 3. Update the model

In `app/Models/User.php`, add the column to `#[Fillable]`:

```php
#[Fillable(['name', 'email', 'password', 'role', 'profile', 'location', 'phone'])]
```

If the column needs casting, add it to `casts()`:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'phone_verified_at' => 'datetime', // example
    ];
}
```

### 4. Update the factory

Add the new field to `database/factories/UserFactory.php` so tests and seeders still work:

```php
'phone' => fake()->phoneNumber(),
```

### 5. Run it

```bash
php artisan migrate
php artisan test
```

## Expected relationships

Once the domain models exist, `User` will have:

```php
public function jobListings()
{
    return $this->hasMany(JobListing::class, 'posted_by');
}

public function applications()
{
    return $this->hasMany(Application::class);
}

public function resumes()
{
    return $this->hasMany(Resume::class);
}

public function savedJobListings()
{
    return $this->belongsToMany(JobListing::class, 'saved_job_listings')
        ->withTimestamps();
}

public function skills()
{
    return $this->belongsToMany(Skill::class, 'user_skill')
        ->withPivot(['proficiency', 'years_of_experience'])
        ->withTimestamps();
}
```

> These models do not exist yet. Add the relationships when the related models are created.

## Auth usage

Normal user routes:

```php
auth()->user();      // current web user
auth('web')->user(); // same thing
```

Admin routes must use the admin guard:

```php
auth('admin')->user();
```

Do not use `auth()->user()` inside `/admin` code — that returns a normal user, not an admin.

## Testing

```php
$seeker = User::factory()->create(['role' => 'seeker']);
$employer = User::factory()->create(['role' => 'employer']);

$this->actingAs($seeker)->get('/dashboard')->assertOk();
```

Run the suite with:

```bash
php artisan test
```

## Common mistakes

- Editing an old migration instead of creating a new one.
- Forgetting to add a new column to `#[Fillable]`.
- Storing a password hash manually instead of letting the cast hash it.
- Adding `admin` to `users.role`.
- Using `auth()->user()` on admin pages.
- Deleting a user without checking related data — `job_listings.posted_by` restricts deletion, while `applications.user_id` currently cascades.
