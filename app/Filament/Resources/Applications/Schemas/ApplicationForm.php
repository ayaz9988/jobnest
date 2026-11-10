<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('job_listing_id')
                    ->required()
                    ->numeric(),
                TextInput::make('resume_id')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options([
            'applied' => 'Applied',
            'reviewed' => 'Reviewed',
            'interview' => 'Interview',
            'withdraw' => 'Withdraw',
            'hired' => 'Hired',
            'rejected' => 'Rejected',
        ])
                    ->default('applied')
                    ->required(),
                Textarea::make('cover_letter')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('applied_at')
                    ->required(),
            ]);
    }
}
