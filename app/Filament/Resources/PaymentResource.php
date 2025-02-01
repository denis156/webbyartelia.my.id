<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Payment;
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
use App\Filament\Resources\PaymentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'fluentui-receipt-money-20-o';
    
    protected static ?string $recordTitleAttribute = 'payment_number';

    protected static ?string $modelLabel = 'Pembayaran';

    protected static ?string $pluralModelLabel = 'Pembayaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        // Left Column
                        Group::make()
                            ->schema([
                                Section::make('Informasi Pembayaran')
                                    ->description('Kelola informasi utama pembayaran di sini.')
                                    ->icon('fluentui-receipt-money-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Select::make('invoice_id')
                                            ->label('Faktur')
                                            ->relationship('invoice', 'invoice_number')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->native(false)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('payment_number')
                                            ->label('Nomor Pembayaran')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder('Nomor pembayaran akan dibuat otomatis')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('amount')
                                            ->rupiah(false)
                                            ->label('Jumlah Pembayaran')
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('payment_method')
                                            ->label('Metode Pembayaran')
                                            ->required()
                                            ->native(false)
                                            ->options([
                                                'cash' => 'Tunai',
                                                'bank_transfer' => 'Transfer Bank'
                                            ])
                                            ->default('bank_transfer'),

                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->required()
                                            ->native(false)
                                            ->options([
                                                'pending' => 'Menunggu',
                                                'verified' => 'Terverifikasi',
                                                'rejected' => 'Ditolak'
                                            ])
                                            ->default('pending'),
                                    ]),
                            ]),

                        // Right Column
                        Group::make()
                            ->schema([
                                Section::make('Bukti & Verifikasi')
                                    ->description('Unggah bukti pembayaran dan verifikasi.')
                                    ->icon('fluentui-document-checkmark-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\FileUpload::make('payment_proof')
                                            ->label('Bukti Pembayaran')
                                            ->image()
                                            ->directory('payment-proofs')
                                            ->visibility('private')
                                            ->maxSize(2048)
                                            ->downloadable()
                                            ->helperText('Maksimal 2MB. Format: JPG, PNG'),

                                        Forms\Components\Select::make('verified_by')
                                            ->label('Diverifikasi Oleh')
                                            ->relationship('verifier', 'name')
                                            ->native(false)
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\DateTimePicker::make('verified_at')
                                            ->label('Waktu Verifikasi')
                                            ->native(false)
                                            ->displayFormat('d M Y H:i'),
                                    ]),

                                Section::make('Catatan')
                                    ->description('Tambahkan catatan pembayaran.')
                                    ->icon('fluentui-text-bullet-list-square-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Textarea::make('payment_notes')
                                            ->label('Catatan Pembayaran')
                                            ->rows(3)
                                            ->placeholder('Tambahkan catatan jika diperlukan'),

                                        Forms\Components\Textarea::make('rejection_reason')
                                            ->label('Alasan Penolakan')
                                            ->rows(3)
                                            ->placeholder('Masukkan alasan penolakan')
                                            ->visible(fn($get) => $get('status') === 'rejected'),
                                    ]),
                            ]),
                    ])->columnSpan('full'),
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

                Tables\Columns\TextColumn::make('payment_number')
                    ->label('No. Pembayaran')
                    ->searchable()
                    ->copyable()
                    ->alignCenter()
                    ->copyMessage('Nomor pembayaran berhasil disalin')
                    ->icon('fluentui-receipt-money-20-o')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('No. Faktur')
                    ->searchable()
                    ->alignCenter()
                    ->icon('fluentui-document-bullet-list-20-o')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'bank_transfer' => 'Transfer Bank',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'info',
                        'bank_transfer' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->colors([
                        'gray' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->alignCenter()
                    ->description(fn($record) => $record->verified_at ? $record->verified_at->format('d M Y H:i') : '')
                    ->placeholder('Belum Diverifikasi'),

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
                    ->options([
                        'pending' => 'Menunggu',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode')
                    ->options([
                        'cash' => 'Tunai',
                        'bank_transfer' => 'Transfer Bank',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Pembayaran')
                        ->icon('fluentui-receipt-money-20')
                        ->color('success'),

                    Tables\Actions\EditAction::make()
                        ->label('Ubah Pembayaran')
                        ->icon('fluentui-money-settings-20')
                        ->color('primary'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus Pembayaran')
                        ->icon('fluentui-delete-20')
                        ->color('danger')
                        ->requiresConfirmation(),

                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-delete-dismiss-20')
                        ->requiresConfirmation(),

                    Tables\Actions\RestoreAction::make()
                        ->label('Kembalikan Pembayaran')
                        ->icon('fluentui-arrow-sync-circle-20')
                        ->color('warning')
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('verify')
                        ->label('Verifikasi Pembayaran')
                        ->icon('fluentui-money-calculator-20')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-money-calculator-20-o')
                        ->modalHeading('Verifikasi Pembayaran')
                        ->modalDescription('Apakah Anda yakin ingin memverifikasi pembayaran ini?')
                        ->modalSubmitActionLabel('Ya, Verifikasi')
                        ->modalCancelActionLabel('Batal')
                        ->visible(fn(Model $record): bool => $record->status === 'pending' && !$record->trashed())
                        ->action(function (Model $record) {
                            $record->verify(Filament::auth()->id());
                            Notification::make()
                                ->success()
                                ->title('Pembayaran berhasil diverifikasi')
                                ->send();
                        }),

                    Tables\Actions\Action::make('reject')
                        ->label('Tolak Pembayaran')
                        ->icon('fluentui-money-dismiss-20')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(3)
                                ->placeholder('Masukkan alasan penolakan pembayaran')
                        ])
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-money-dismiss-20-o')
                        ->modalHeading('Tolak Pembayaran')
                        ->modalDescription('Apakah Anda yakin ingin menolak pembayaran ini?')
                        ->modalSubmitActionLabel('Ya, Tolak')
                        ->modalCancelActionLabel('Batal')
                        ->visible(fn(Model $record): bool => $record->status === 'pending' && !$record->trashed())
                        ->action(function (Model $record, array $data) {
                            $record->reject($data['rejection_reason']);
                            Notification::make()
                                ->success()
                                ->title('Pembayaran berhasil ditolak')
                                ->send();
                        }),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan')
                    ->icon('fluentui-money-settings-20'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Massal')
                        ->icon('fluentui-delete-20')
                        ->color('danger'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-delete-dismiss-20')
                        ->color('danger'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Kembalikan Massal')
                        ->icon('fluentui-arrow-sync-circle-20')
                        ->color('warning'),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan Massal')
                    ->icon('fluentui-money-settings-20'),
            ])
            ->emptyStateHeading('Belum ada pembayaran')
            ->emptyStateDescription('Mulai dengan membuat pembayaran baru.')
            ->emptyStateIcon('fluentui-receipt-money-20-o')
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'view' => Pages\ViewPayment::route('/{record}'),
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
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();

        return match (true) {
            $pendingCount >= 5 => 'danger',     // Jika ada 5+ pembayaran pending
            $pendingCount >= 3 => 'warning',    // Jika ada 3+ pembayaran pending
            default => 'primary',               // Jika kurang dari 3 pending
        };
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        return "Pembayaran Menunggu: {$pendingCount}";
    }
}
