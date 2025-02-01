<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PaymentResource;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verify')
                ->label('Verifikasi Pembayaran')
                ->icon('fluentui-money-calculator-20')
                ->color('success')
                ->requiresConfirmation()
                ->modalIcon('fluentui-money-calculator-20-o')
                ->modalHeading('Verifikasi Pembayaran')
                ->modalDescription('Apakah Anda yakin ingin memverifikasi pembayaran ini?')
                ->modalSubmitActionLabel('Ya, Verifikasi')
                ->modalCancelActionLabel('Batal')
                ->visible(fn() => $this->record->status === 'pending' && !$this->record->trashed())
                ->action(function () {
                    $this->record->verify(Filament::auth()->id());
                    Notification::make()
                        ->success()
                        ->title('Pembayaran berhasil diverifikasi')
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\Action::make('reject')
                ->label('Tolak Pembayaran')
                ->icon('fluentui-money-dismiss-20')
                ->color('danger')
                ->form([
                    TextInput::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->placeholder('Masukkan alasan penolakan pembayaran'),
                ])
                ->requiresConfirmation()
                ->modalIcon('fluentui-money-dismiss-20-o')
                ->modalHeading('Tolak Pembayaran')
                ->modalDescription('Apakah Anda yakin ingin menolak pembayaran ini?')
                ->modalSubmitActionLabel('Ya, Tolak')
                ->modalCancelActionLabel('Batal')
                ->visible(fn() => $this->record->status === 'pending' && !$this->record->trashed())
                ->action(function (array $data) {
                    $this->record->reject($data['rejection_reason']);
                    Notification::make()
                        ->success()
                        ->title('Pembayaran berhasil ditolak')
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Actions\DeleteAction::make()
                ->label('Hapus Pembayaran')
                ->color('danger')
                ->icon('fluentui-receipt-money-20')
                ->modalIcon('fluentui-receipt-money-20-o'),

            Actions\ForceDeleteAction::make()
                ->label('Hapus Selamanya')
                ->color('danger')
                ->icon('fluentui-cloud-off-20')
                ->modalIcon('fluentui-cloud-off-20-o'),

            Actions\RestoreAction::make()
                ->label('Kembalikan Pembayaran')
                ->color('warning')
                ->icon('fluentui-cloud-sync-20')
                ->modalIcon('fluentui-cloud-sync-20-o'),
        ];
    }
}
