<?php

namespace App\Filament\Resources\Resumes\Schemas;

use App\Support\AdminTranslations;
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
                    ->label(AdminTranslations::field('user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                FileUpload::make('file_path')
                    ->label(AdminTranslations::field('resume_file'))
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
                    ->helperText(__('admin.helper_text.resume_file')),
                Toggle::make('is_primary')
                    ->label(AdminTranslations::field('is_primary'))
                    ->required(),
            ]);
    }
}
