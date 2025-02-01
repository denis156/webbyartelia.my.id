<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'fluentui-document-bullet-list-multiple-20-o';

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static ?string $modelLabel = 'Faktur';

    protected static ?string $pluralModelLabel = 'Faktur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'md' => 2])
                    ->schema([
                        // Left Column
                        Section::make('Informasi Faktur')
                            ->description('Kelola informasi utama faktur di sini.')
                            ->icon('fluentui-document-bullet-list-20')
                            ->collapsible()
                            ->schema([
                                Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        Forms\Components\Select::make('project_id')
                                            ->label('Proyek')
                                            ->relationship('project', 'project_name', fn(Builder $query) => $query->where('status', 'approved'))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->columnSpanFull()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                if ($state) {
                                                    try {
                                                        $project = \App\Models\Project::find($state);

                                                        if (!$project) {
                                                            return;
                                                        }

                                                        $baseAmount = (float) $project->price;
                                                        $taxPercent = (float) str_replace([',', '%'], '', $get('tax_amount') ?? 0);
                                                        $taxAmount = $baseAmount * ($taxPercent / 100);
                                                        $totalWithTax = $baseAmount + $taxAmount;

                                                        $set('total_amount', number_format($totalWithTax, 2, '.', ','));

                                                        $paidAmount = $get('paid_amount')
                                                            ? (float) str_replace([',', 'Rp', ' '], '', $get('paid_amount'))
                                                            : 0;

                                                        $remaining = $totalWithTax - $paidAmount;
                                                        $set('remaining_amount', number_format($remaining, 2, '.', ','));
                                                    } catch (\Exception $e) {
                                                        Notification::make()
                                                            ->title('Error saat memproses data proyek')
                                                            ->danger()
                                                            ->send();
                                                    }
                                                } else {
                                                    $set('total_amount', '0.00');
                                                    $set('remaining_amount', '0.00');
                                                    $set('tax_amount', '0.00');
                                                    $set('paid_amount', '0.00');
                                                }
                                            })
                                            ->placeholder('Pilih Proyek di sini'),

                                        Forms\Components\TextInput::make('invoice_number')
                                            ->label('Nomor Faktur')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull()
                                            ->placeholder('Nomor faktur akan dibuat otomatis'),

                                        Forms\Components\Select::make('payment_type')
                                            ->label('Tipe Pembayaran')
                                            ->required()
                                            ->native(false)
                                            ->options([
                                                'full' => 'Bayar Lunas',
                                                'partial' => 'Bayar Bertahap'
                                            ])
                                            ->default('full')
                                            ->placeholder('Pilih tipe pembayaran di sini'),

                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->required()
                                            ->native(false)
                                            ->options([
                                                'draft' => 'Draft',
                                                'sent' => 'Terkirim',
                                                'partially_paid' => 'Bayar Sebagian',
                                                'paid' => 'Dibayar Lunas',
                                                'cancelled' => 'Dibatalkan'
                                            ])
                                            ->default('draft')
                                            ->placeholder('Pilih status di sini'),
                                    ]),

                                Section::make('Rincian Pembayaran')
                                    ->description('Kelola rincian pembayaran di sini.')
                                    ->icon('fluentui-money-hand-20')
                                    ->collapsible()
                                    ->compact()
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                Forms\Components\TextInput::make('tax_amount')
                                                    ->pajak()
                                                    ->label('Pajak')
                                                    ->columnSpanFull()
                                                    ->placeholder('0.00 %'),

                                                Forms\Components\TextInput::make('total_amount')
                                                    ->rupiah(true)
                                                    ->label('Total Tagihan')
                                                    ->required()
                                                    ->placeholder('Tagihan akan otomatis dihitung')
                                                    ->columnSpanFull(),

                                                Forms\Components\TextInput::make('paid_amount')
                                                    ->rupiah(false, true)
                                                    ->label('Jumlah Dibayar')
                                                    ->required()
                                                    ->default(0)
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('remaining_amount')
                                                    ->rupiah(true)
                                                    ->label('Sisa Pembayaran')
                                                    ->required()
                                                    ->default(0)
                                                    ->columnSpan(1),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        // Right Column
                        Grid::make(1)
                            ->schema([
                                Section::make('Tanggal & Waktu')
                                    ->description('Atur tanggal penerbitan dan jatuh tempo.')
                                    ->icon('fluentui-document-bullet-list-clock-20')
                                    ->collapsible()
                                    ->compact()
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                Forms\Components\DatePicker::make('issue_date')
                                                    ->label('Tanggal Penerbitan')
                                                    ->required()
                                                    ->native(false)
                                                    ->displayFormat('d M Y')
                                                    ->default(now())
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        if ($state) {
                                                            $set('due_date', Carbon::parse($state)->addDays(7));
                                                        }
                                                    }),

                                                Forms\Components\DatePicker::make('due_date')
                                                    ->label('Jatuh Tempo')
                                                    ->required()
                                                    ->native(false)
                                                    ->displayFormat('d M Y')
                                                    ->default(fn(): string => now()->addDays(7))
                                                    ->after('issue_date'),

                                                Forms\Components\DateTimePicker::make('sent_at')
                                                    ->label('Waktu Pengiriman')
                                                    ->native(false)
                                                    ->displayFormat('d M Y H:i'),

                                                Forms\Components\DateTimePicker::make('paid_at')
                                                    ->label('Waktu Pembayaran')
                                                    ->native(false)
                                                    ->displayFormat('d M Y H:i'),
                                            ]),
                                    ]),

                                Section::make('Informasi Tambahan')
                                    ->description('Catatan dan informasi pendukung faktur.')
                                    ->icon('fluentui-document-endnote-20')
                                    ->collapsible()
                                    ->compact()
                                    ->schema([
                                        Forms\Components\TextInput::make('created_by')
                                            ->label('Dibuat Oleh')
                                            ->afterStateHydrated(function ($component, $state, ?Model $record) {
                                                $creator = User::find($state);
                                                $component->state($creator?->name ?? Filament::auth()->user()->name);
                                            })
                                            ->disabled()
                                            ->dehydrated(false),

                                        Forms\Components\Textarea::make('notes')
                                            ->label('Catatan')
                                            ->rows(3)
                                            ->placeholder('Tambahkan catatan jika diperlukan')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->rowIndex()
                    ->weight(FontWeight::Bold)
                    ->alignLeft(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Faktur')
                    ->searchable()
                    ->copyable()
                    ->alignCenter()
                    ->copyMessage('Nomor faktur berhasil disalin')
                    ->icon('fluentui-document-bullet-list-multiple-20-o')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('project.project_name')
                    ->label('Proyek')
                    ->searchable()
                    ->alignCenter()
                    ->description(fn($record) => str()->limit(strip_tags($record->project->description), 50))
                    ->icon('fluentui-archive-20-o')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('project.user.name')
                    ->label('Klien')
                    ->searchable()
                    ->alignCenter()
                    ->description(fn($record) => $record->project->user->email ?? '')
                    ->icon('fluentui-person-mail-20-o')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Tagihan')
                    ->money('IDR')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->tax_amount > 0 ? "Pajak: {$record->tax_amount}%" : null),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->alignCenter()
                    ->description(fn($record) => $record->paid_at ? "Dibayar pada: " . $record->paid_at->format('d M Y') : null),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->money('IDR')
                    ->alignCenter()
                    ->color(fn($record) => $record->remaining_amount > 0 ? 'danger' : 'success')
                    ->description(fn($record) => $record->remaining_amount > 0 ? "Belum Lunas" : "Lunas"),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'partially_paid',
                        'success' => ['paid', 'sent'],
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'fluentui-send-clock-20' => 'draft',
                        'fluentui-mail-checkmark-20' => 'sent',
                        'fluentui-receipt-money-20' => 'paid',
                        'fluentui-money-calculator-20' => 'partially_paid',
                        'fluentui-dismiss-circle-20' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'paid' => 'Lunas',
                        'partially_paid' => 'Bayar Sebagian',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : 'gray')
                    ->description(fn($record) => $record->due_date?->diffForHumans()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->toggleable()
                    ->alignCenter()
                    ->description(fn($record) => $record->created_at->diffForHumans())
                    ->tooltip(
                        fn($record): string =>
                        "Dibuat: {$record->created_at->format('d M Y H:i')}\n" .
                            "Diupdate: {$record->updated_at->format('d M Y H:i')}"
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Terhapus')
                    ->indicator('Terhapus'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'paid' => 'Lunas',
                        'partially_paid' => 'Dibayar Sebagian',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->label('Jatuh Tempo')
                    ->query(fn(Builder $query) => $query->overdue())
                    ->indicator('Jatuh Tempo'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Faktur')
                        ->icon('fluentui-document-table-search-20')
                        ->color('success'),

                    Tables\Actions\EditAction::make()
                        ->label('Ubah Faktur')
                        ->icon('fluentui-document-edit-20')
                        ->color('primary'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus Faktur')
                        ->icon('fluentui-document-dismiss-20')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-document-dismiss-20-o'),

                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-document-bullet-list-off-20')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-document-bullet-list-off-20-o'),

                    Tables\Actions\RestoreAction::make()
                        ->label('Kembalikan Faktur')
                        ->icon('fluentui-document-sync-20')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-document-sync-20-o'),

                    Tables\Actions\Action::make('send')
                        ->label('Kirim')
                        ->icon('fluentui-document-arrow-up-20')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-document-arrow-up-20-o')
                        ->modalHeading('Kirim Faktur')
                        ->modalDescription('Apakah Anda yakin ingin mengirim faktur ini?')
                        ->visible(fn(Model $record) => $record->status === 'draft' && !$record->trashed())
                        ->action(fn(Model $record) => $record->update([
                            'status' => 'sent',
                            'sent_at' => now()
                        ])),

                    Tables\Actions\Action::make('mark_as_paid')
                        ->label('Tandai Lunas')
                        ->icon('fluentui-document-table-checkmark-20')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-document-table-checkmark-20-o')
                        ->modalHeading('Tandai Lunas Faktur')
                        ->modalDescription('Apakah Anda yakin ingin tandai faktur ini sebagai lunas?')
                        ->visible(fn(Model $record) => in_array($record->status, ['sent', 'partially_paid']) && !$record->trashed())
                        ->action(fn(Model $record) => $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'paid_amount' => $record->total_amount,
                            'remaining_amount' => 0
                        ])),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan')
                    ->icon('fluentui-document-settings-20')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Massal')
                        ->icon('fluentui-document-dismiss-20')
                        ->color('danger')
                        ->modalIcon('fluentui-document-dismiss-20-o'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-document-multiple-prohibited-20')
                        ->color('danger')
                        ->modalIcon('fluentui-document-multiple-prohibited-20-o'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Kembalikan Massal')
                        ->icon('fluentui-document-sync-20')
                        ->color('warning'),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan Massal')
                    ->icon('fluentui-document-settings-20'),
            ])
            ->emptyStateHeading('Belum ada faktur')
            ->emptyStateDescription('Mulai dengan membuat faktur baru.')
            ->emptyStateIcon('fluentui-document-bullet-list-multiple-20-o')
            ->poll('30s')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $draftCount = static::getModel()::where('status', 'draft')->count();

        return match (true) {
            $draftCount >= 5 => 'danger',     // Jika ada 5+ faktur draft
            $draftCount >= 3 => 'warning',    // Jika ada 3+ faktur draft
            default => 'primary',             // Jika kurang dari 3 draft
        };
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $draftCount = static::getModel()::where('status', 'draft')->count();
        return "Faktur Draft: {$draftCount}";
    }
}
