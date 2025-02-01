<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PaymentResource;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.resources.payment-resource.pages.view-payment';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Bukti Pembayaran')
                ->color('success')
                ->button()
                ->icon('fluentui-print-add-20')
                ->extraAttributes([
                    'onclick' => 'window.print()',
                ])
                ->visible(fn($record) => !$record->trashed()),

            Actions\EditAction::make()
                ->label('Ubah Pembayaran')
                ->color('primary')
                ->icon('fluentui-money-settings-20'),

            Actions\DeleteAction::make()
                ->label('Hapus Pembayaran')
                ->color('danger')
                ->icon('fluentui-delete-20')
                ->modalIcon('fluentui-delete-20-o'),

            Actions\ForceDeleteAction::make()
                ->label('Hapus Selamanya')
                ->color('danger')
                ->icon('fluentui-delete-dismiss-20')
                ->modalIcon('fluentui-delete-dismiss-20-o'),

            Actions\RestoreAction::make()
                ->label('Kembalikan Pembayaran')
                ->color('warning')
                ->icon('fluentui-arrow-sync-circle-20')
                ->modalIcon('fluentui-arrow-sync-circle-20-o'),
        ];
    }
}
