<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Str;

trait HasTranslatedResourceLabels
{
    public static function getModelLabel(): string
    {
        return __(static::translationKey().'.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __(static::translationKey().'.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __(static::translationKey().'.navigation');
    }

    public static function getBreadcrumb(): string
    {
        return __(static::translationKey().'.navigation');
    }

    protected static function translationKey(): string
    {
        return 'admin.resources.'.Str::of(class_basename(static::class))
            ->beforeLast('Resource')
            ->snake()
            ->toString();
    }
}
