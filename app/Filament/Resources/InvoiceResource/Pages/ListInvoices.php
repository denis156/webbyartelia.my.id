<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\Invoice;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Faktur')
                ->icon('fluentui-document-bullet-list-multiple-20'),

            'draft' => Tab::make('Draft')
                ->icon('fluentui-send-clock-20')
                ->badge(fn() => Invoice::where('status', 'draft')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'draft')),

            'sent' => Tab::make('Terkirim')
                ->icon('fluentui-mail-checkmark-20')
                ->badge(fn() => Invoice::where('status', 'sent')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'sent')),

            'partially_paid' => Tab::make('Bayar Sebagian')
                ->icon('fluentui-money-calculator-20')
                ->badge(fn() => Invoice::where('status', 'partially_paid')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'partially_paid')),

            'paid' => Tab::make('Bayar Lunas')
                ->icon('fluentui-receipt-money-20')
                ->badge(fn() => Invoice::where('status', 'paid')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'paid')),

            'cancelled' => Tab::make('Dibatalkan')
                ->icon('fluentui-dismiss-circle-20')
                ->badge(fn() => Invoice::where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cancelled')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Faktur Baru')
                ->icon('fluentui-document-add-20'),
        ];
    }
}
