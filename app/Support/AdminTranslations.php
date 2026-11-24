<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

final class AdminTranslations
{
    public static function field(string $field): string
    {
        $key = 'admin.fields.'.$field;

        return Lang::has($key) ? __($key) : str($field)->headline()->toString();
    }

    /**
     * @return array<string, string>
     */
    public static function options(string $group): array
    {
        return (array) __('admin.options.'.$group);
    }

    public static function option(string $group, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $key = 'admin.options.'.$group.'.'.$value;

        return Lang::has($key) ? __($key) : $value;
    }
}
