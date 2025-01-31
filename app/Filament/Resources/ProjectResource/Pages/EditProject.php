<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus Proyek')
                ->color('danger')
                ->icon('fluentui-cloud-dismiss-20')
                ->modalIcon('fluentui-cloud-dismiss-20-o'),
            Actions\ForceDeleteAction::make()
                ->label('Hapus Selamanya')
                ->color('danger')
                ->icon('fluentui-cloud-off-20')
                ->modalIcon('fluentui-cloud-off-20-o'),
            Actions\RestoreAction::make()
                ->label('Kembalikan Proyek')
                ->color('warning')
                ->icon('fluentui-cloud-sync-20')
                ->modalIcon('fluentui-cloud-sync-20-o'),
        ];
    }
}
