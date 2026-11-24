<?php

namespace App\Filament\Resources\JobListings\Schemas;

use App\Support\AdminTranslations;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class JobListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('posted_by')
                    ->label(AdminTranslations::field('posted_by'))
                    ->relationship('postedBy', 'name',
                        fn (Builder $query) => $query->where('role', 'employer')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label(AdminTranslations::field('category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->label(AdminTranslations::field('title'))
                    ->required(),
                Textarea::make('description')
                    ->label(AdminTranslations::field('description'))
                    ->required()
                    ->columnSpanFull(),
                Select::make('employment_type')
                    ->label(AdminTranslations::field('employment_type'))
                    ->options(AdminTranslations::options('employment_types'))
                    ->required(),
                Select::make('workplace_type')
                    ->label(AdminTranslations::field('workplace_type'))
                    ->options(AdminTranslations::options('workplace_types'))
                    ->required(),
                TextInput::make('location')
                    ->label(AdminTranslations::field('location'))
                    ->default(null),
                TextInput::make('salary_min')
                    ->label(AdminTranslations::field('salary_min'))
                    ->numeric()
                    ->default(null),
                TextInput::make('salary_max')
                    ->label(AdminTranslations::field('salary_max'))
                    ->numeric()
                    ->default(null),
                TextInput::make('salary_currency')
                    ->label(AdminTranslations::field('salary_currency'))
                    ->default(null),
                Select::make('status')
                    ->label(AdminTranslations::field('status'))
                    ->options(AdminTranslations::options('job_statuses'))
                    ->default('open')
                    ->required(),
                DateTimePicker::make('expires_at')
                    ->label(AdminTranslations::field('expires_at')),
                DateTimePicker::make('published_at')
                    ->label(AdminTranslations::field('published_at')),
            ]);
    }
}
