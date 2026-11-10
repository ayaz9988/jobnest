<?php

namespace App\Filament\Resources\Resumes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ResumeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                FileUpload::make('file_path')
                    ->label('Resume file')
                    ->disk('local')
                    ->directory('resumes')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->maxSize(5120)
                    ->storeFileNamesIn('file_name')
                    ->preventFilePathTampering()
                    ->required()
                    ->helperText('PDF, DOC or DOCX up to 5 MB.'),
                Toggle::make('is_primary')
                    ->required(),
            ]);
    }
}
