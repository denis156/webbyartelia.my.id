<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\InvoiceResource;


class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static string $view = 'filament.resources.invoice-resource.pages.view-invoice';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Faktur')
                ->color('success')
                ->button()
                ->icon('fluentui-print-add-20')
                ->extraAttributes([
                    'onclick' => 'window.print()',
                ])
                ->visible(fn($record) => !$record->trashed()),

            Actions\EditAction::make()
                ->label('Ubah Faktur')
                ->color('primary')
                ->icon('fluentui-document-edit-20'),

            Actions\DeleteAction::make()
                ->label('Hapus Faktur')
                ->color('danger')
                ->icon('fluentui-document-dismiss-20')
                ->modalIcon('fluentui-document-dismiss-20-o'),

            Actions\ForceDeleteAction::make()
                ->label('Hapus Selamanya')
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
