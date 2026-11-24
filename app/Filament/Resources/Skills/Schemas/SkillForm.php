<?php

namespace App\Filament\Resources\Skills\Schemas;

use App\Support\AdminTranslations;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SkillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(AdminTranslations::field('name'))
                    ->required(),
            ]);
    }
}
