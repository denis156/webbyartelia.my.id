<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class PajakInput extends TextInput
{
    public static function make(string $name): static
    {
        return parent::make($name);
    }

    public function pajak(): static
    {
        $this->required()
            ->suffix('%')
            ->maxValue(100)
            ->minValue(0)
            ->step(0.01)
            ->default(0)
            ->rules(['numeric', 'min:0', 'max:100'])
            ->placeholder('0.00')
            ->live()
            ->hint(function ($get) {
                if ($get('project_id')) {
                    $project = \App\Models\Project::find($get('project_id'));
                    if (!$project) return;

                    $baseAmount = (float) $project->price;
                    $taxPercent = (float) ($get('tax_amount') ?? 0);
                    $taxAmount = ($baseAmount * $taxPercent) / 100;

                    return 'Tagihan: Rp ' . number_format($baseAmount, 2) . ' + Pajak: Rp ' . number_format($taxAmount, 2);
                }
                return 'Tagihan: Rp 0.00 + Pajak: Rp 0.00';
            })
            ->afterStateUpdated(function ($state, $set, $get) {
                if ($get('project_id')) {
                    try {
                        $project = \App\Models\Project::find($get('project_id'));
                        if (!$project) return;

                        $baseAmount = (float) $project->price;
                        $taxPercent = (float) ($state ?? 0);
                        $taxAmount = ($baseAmount * $taxPercent) / 100;
                        $totalWithTax = $baseAmount + $taxAmount;

                        $set('total_amount', number_format($totalWithTax, 2));

                        $paidAmount = (float) str_replace(',', '',
                            preg_replace('/[^0-9,.]/', '', $get('paid_amount') ?? '0')
                        );
                        $remaining = $totalWithTax - $paidAmount;
                        $set('remaining_amount', number_format($remaining, 2));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error saat menghitung pajak')
                            ->danger()
                            ->send();
                    }
                }
            });

        return $this;
    }
}
