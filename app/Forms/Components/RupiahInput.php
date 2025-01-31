<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Filament\Notifications\Notification;

class RupiahInput extends TextInput
{
    public static function make(string $name): static
    {
        return parent::make($name);
    }

    public function rupiah(bool $isReadOnly = false, bool $isLive = false): static
    {
        $this->prefix('Rp')
            ->formatStateUsing(function ($state) {
                if (is_numeric($state)) {
                    return number_format($state, 2);
                }
                return $state;
            })
            ->dehydrateStateUsing(function ($state) {
                if ($state) {
                    return (float) str_replace(
                        ',',
                        '',
                        preg_replace('/[^0-9,.]/', '', $state)
                    );
                }
                return null;
            })
            ->mask(RawJs::make(<<<'JS'
                $money($input)
            JS))
            ->readOnly($isReadOnly);

        if ($isLive) {
            $this->live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    try {
                        $totalAmount = (float) str_replace(',', '',
                            preg_replace('/[^0-9,.]/', '', $get('total_amount') ?? '0')
                        );
                        $paidAmount = (float) str_replace(',', '',
                            preg_replace('/[^0-9,.]/', '', $state ?? '0')
                        );
                        $remaining = $totalAmount - $paidAmount;
                        $set('remaining_amount', number_format($remaining, 2));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error saat menghitung sisa pembayaran')
                            ->danger()
                            ->send();
                    }
                });
        }

        return $this;
    }
}
