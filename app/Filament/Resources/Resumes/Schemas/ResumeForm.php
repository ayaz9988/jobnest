<?php

namespace App\Filament\Resources\Resumes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ResumeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('file_url')
                    ->url()
                    ->required(),
                TextInput::make('file_name')
                    ->required(),
                TextInput::make('mime_type')
                    ->default(null),
                TextInput::make('file_size')
                    ->numeric()
                    ->default(null),
                Toggle::make('is_primary')
                    ->required(),
            ]);
    }
}
