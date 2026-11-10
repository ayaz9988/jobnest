<?php

namespace App\Filament\Resources\JobListings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class JobListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('posted_by')
                    ->required()
                    ->numeric(),
                TextInput::make('category_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
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
                    ->options(['on_site' => 'On site', 'remote' => 'Remote', 'hybrid' => 'Hybrid'])
                    ->required(),
                TextInput::make('location')
                    ->default(null),
                TextInput::make('salary_min')
                    ->numeric()
                    ->default(null),
                TextInput::make('salary_max')
                    ->numeric()
                    ->default(null),
                TextInput::make('salary_currency')
                    ->default(null),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'open' => 'Open', 'closed' => 'Closed', 'filled' => 'Filled'])
                    ->default('open')
                    ->required(),
                DateTimePicker::make('expires_at'),
                DateTimePicker::make('published_at'),
            ]);
    }
}
