<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->color('danger')
                ->icon('fluentui-person-delete-20-o')
                ->label('Hapus Pengguna')
                ->requiresConfirmation()
                ->modalIcon('fluentui-person-delete-20-o'),
            Actions\ForceDeleteAction::make()
                ->color('danger')
                ->icon('fluentui-person-prohibited-20')
                ->label('Hapus Selamanya')
                ->requiresConfirmation()
                ->modalIcon('fluentui-person-prohibited-20-o'),
            Actions\RestoreAction::make()
                ->color('warning')
                ->icon('fluentui-person-sync-20')
                ->label('Kembalikan Pengguna')
                ->requiresConfirmation()
                ->modalIcon('fluentui-person-sync-20-o'),
        ];
    }
}
