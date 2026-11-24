<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use App\Support\AdminTranslations;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(AdminTranslations::field('name'))
                    ->required(),
                TextInput::make('slug')
                    ->label(AdminTranslations::field('slug'))
                    ->required(),
                Select::make('parent_id')
                    ->label(AdminTranslations::field('parent_category'))
                    ->relationship('parent', 'name',
                        fn (Builder $query, ?Category $record) => $query
                            ->when($record, fn (Builder $query) => $query
                                ->whereNotIn('id', $record->descendantIds())),
                        ignoreRecord: true,
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder(__('admin.placeholders.none_top_level'))
                    ->rule(
                        fn (?Category $record) => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                            if (! $record || blank($value)) {
                                return;
                            }

                            if ((int) $value === $record->getKey() || in_array((int) $value, $record->descendantIds(), true)) {
                                $fail(__('admin.errors.category_parent_self'));
                            }
                        }
                    ),
            ]);
    }
}
