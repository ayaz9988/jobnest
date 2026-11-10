<?php

namespace App\Filament\Resources\Resumes\Pages;

use App\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class EditResume extends EditRecord
{
    protected static string $resource = ResumeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(fn (): string => route(
                    'filament.admin.resumes.download',
                    ['resume' => $this->getRecord()],
                ))
                ->openUrlInNewTab()
                ->visible(fn (): bool => Gate::allows('download', $this->getRecord())),
            DeleteAction::make(),
        ];
    }
}
