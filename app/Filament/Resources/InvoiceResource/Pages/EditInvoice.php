<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\InvoiceResource;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Faktur')
                ->color('success')
                ->icon('fluentui-document-table-search-20'),
            Actions\DeleteAction::make()
                ->color('danger')
                ->icon('fluentui-document-dismiss-20')
                ->modalIcon('fluentui-document-dismiss-20-o'),
            Actions\ForceDeleteAction::make()
                ->color('danger')
                ->icon('fluentui-document-prohibited-20')
                ->modalIcon('fluentui-document-prohibited-20-o'),
            Actions\RestoreAction::make()
                ->label('Kembalikan Faktur')
                ->color('warning')
                ->icon('fluentui-document-sync-20')
                ->modalIcon('fluentui-document-sync-20-o'),
        ];
    }
}
