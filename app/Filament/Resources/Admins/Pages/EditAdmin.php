<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Actions\Admins\DeleteAdmin;
use App\Actions\Admins\UpdateAdmin;
use App\Filament\Resources\Admins\AdminResource;
use App\Models\Admin;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof Admin) {
            throw new LogicException('Expected an admin record.');
        }

        return app(UpdateAdmin::class)->handle(
            auth('admin')->user(),
            $record,
            $data,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(function (Admin $record): bool {
                    return app(DeleteAdmin::class)->handle(
                        auth('admin')->user(),
                        $record,
                    );
                }),
        ];
    }
}
