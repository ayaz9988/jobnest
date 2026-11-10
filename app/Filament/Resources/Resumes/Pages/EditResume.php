<?php

namespace App\Filament\Resources\Resumes\Pages;

use App\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResume extends EditRecord
{
    protected static string $resource = ResumeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
