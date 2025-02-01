<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Project;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProjectResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProjectResource\RelationManagers;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'fluentui-cloud-archive-20-o';

    protected static ?string $recordTitleAttribute = 'project_name';

    protected static ?string $modelLabel = 'Proyek';

    protected static ?string $pluralModelLabel = 'Proyek';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        // Kolom Kiri
                        Group::make()
                            ->schema([
                                Section::make('Informasi Proyek')
                                    ->description('Kelola informasi utama proyek di sini.')
                                    ->icon('fluentui-archive-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Klien')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->native(false),

                                        Forms\Components\TextInput::make('project_name')
                                            ->label('Nama Proyek')
                                            ->required()
                                            ->maxLength(100)
                                            ->minLength(3)
                                            ->helperText(fn($state): string => 'Sisa karakter: ' . (100 - strlen($state)))
                                            ->placeholder('Masukkan nama proyek (maks. 100 karakter)'),

                                        Forms\Components\RichEditor::make('description')
                                            ->label('Deskripsi')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                            ])
                                            ->maxLength(200) // Batasan 200 karakter
                                            ->helperText(fn($state): string => 'Sisa karakter: ' . (200 - str_word_count($state)))
                                            ->placeholder('Masukkan deskripsi singkat proyek (maksimal 200 karakter)')
                                            ->columnSpanFull()
                                    ]),

                                Section::make('Pengaturan Harga & Status')
                                    ->description('Atur harga dan status proyek.')
                                    ->icon('fluentui-receipt-money-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->rupiah(false)
                                            ->label('Harga')
                                            ->required()
                                            ->placeholder('Nominal proyek'),

                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->required()
                                            ->native(false)
                                            ->options([
                                                'pending' => 'Menunggu',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                                'in_progress' => 'Dalam Pengerjaan',
                                                'completed' => 'Selesai',
                                                'cancelled' => 'Dibatalkan',
                                            ])
                                            ->default('pending'),

                                        Forms\Components\TextInput::make('progress')
                                            ->label('Progress')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->placeholder('0'),
                                    ]),
                            ]),

                        // Kolom Kanan
                        Group::make()
                            ->schema([
                                Section::make('Jadwal Proyek')
                                    ->description('Atur jadwal dan tenggat waktu proyek.')
                                    ->icon('fluentui-calendar-info-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Tanggal Mulai')
                                            ->native(false)
                                            ->displayFormat('d M Y'),

                                        Forms\Components\DatePicker::make('deadline')
                                            ->label('Tenggat Waktu')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->after('start_date'),

                                        Forms\Components\DatePicker::make('completion_date')
                                            ->label('Tanggal Selesai')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->after('start_date'),
                                    ]),

                                Section::make('Informasi Tambahan')
                                    ->description('Tambahkan persyaratan dan dokumen pendukung.')
                                    ->icon('fluentui-cloud-words-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\Textarea::make('requirements')
                                            ->label('Persyaratan')
                                            ->placeholder('Masukkan persyaratan proyek')
                                            ->rows(3),

                                        Forms\Components\FileUpload::make('attachment_path')
                                            ->label('Lampiran')
                                            ->multiple()
                                            ->disk('public')
                                            ->directory(function ($record) {
                                                return 'project-attachments/' . str($record?->project_name ?? 'temp')->slug();
                                            })
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->maxSize(3072)
                                            ->downloadable()
                                            ->reorderable()
                                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                                            ->helperText('Maksimal 3MB. Format: PDF, Gambar'),

                                        Forms\Components\Textarea::make('rejection_reason')
                                            ->label('Alasan Penolakan')
                                            ->placeholder('Masukkan alasan penolakan')
                                            ->rows(3)
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

                Tables\Columns\ImageColumn::make('attachment_path')
                    ->label('Lampiran')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(size: 'lg')
                    ->checkFileExistence(false)
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->defaultImageUrl(fn() => asset('images/project-placeholder.png')),

                Tables\Columns\TextColumn::make('project_name')
                    ->label('Nama Proyek')
                    ->searchable()
                    ->copyable()
                    ->alignCenter()
                    ->copyMessage('Nama proyek berhasil disalin')
                    ->limit(50)
                    ->tooltip(function ($state) {
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->icon('fluentui-document-bullet-list-20-o')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Klien')
                    ->searchable()
                    ->description(fn($record) => $record->user->email ?? '')
                    ->icon('fluentui-person-mail-20-o')
                    ->iconColor('primary')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->alignEnd()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->colors([
                        'danger' => 'rejected',
                        'warning' => 'pending',
                        'success' => 'completed',
                        'info' => 'in_progress',
                        'gray' => 'cancelled',
                    ])
                    ->icons([
                        'fluentui-arrow-sync-circle-20' => 'pending',
                        'fluentui-checkmark-circle-20' => 'approved',
                        'fluentui-dismiss-circle-20' => 'rejected',
                        'fluentui-arrow-clockwise-20' => 'in_progress',
                        'fluentui-flag-20' => 'completed',
                        'fluentui-dismiss-square-20' => 'cancelled',
                    ])
                    ->iconPosition('before') // bisa gunakan 'before' atau 'after'
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'in_progress' => 'Dalam Pengerjaan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->alignCenter()
                    ->formatStateUsing(fn(string $state): string => "{$state}%")
                    ->color(fn($state) => match (true) {
                        $state >= 70 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->icon('fluentui-cloud-flow-20-o')
                    ->description(
                        fn($record) =>
                        $record->progress == 100
                            ? 'Selesai'
                            : 'Pengerjaan'
                    ),

                Tables\Columns\TextColumn::make('deadline')
                    ->label('Tenggat Waktu')
                    ->date('d M Y')
                    ->alignCenter()
                    ->toggleable()
                    ->description(fn($record) => $record->deadline?->diffForHumans())
                    ->color(
                        fn($record) =>
                        $record->deadline?->isPast() && $record->status !== 'completed'
                            ? 'danger'
                            : 'gray'
                    ),

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
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->indicator('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'in_progress' => 'Dalam Pengerjaan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Klien')
                    ->relationship('user', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->indicator('Klien'),

                Tables\Filters\Filter::make('deadline')
                    ->label('Tenggat Waktu')
                    ->form([
                        Forms\Components\DatePicker::make('deadline_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('deadline_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->indicator('Tenggat Waktu')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['deadline_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('deadline', '>=', $date),
                            )
                            ->when(
                                $data['deadline_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('deadline', '<=', $date),
                            );
                    })
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah Proyek')
                        ->icon('fluentui-cloud-edit-20')
                        ->color('primary'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus Proyek')
                        ->icon('fluentui-cloud-dismiss-20')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-cloud-dismiss-20-o'),

                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-cloud-off-20')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-cloud-off-20-o'),

                    Tables\Actions\RestoreAction::make()
                        ->label('Kembalikan Proyek')
                        ->color('warning')
                        ->icon('fluentui-cloud-sync-20')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-cloud-sync-20-o'),

                    Tables\Actions\Action::make('approve')
                        ->label('Setujui Proyek')
                        ->icon('fluentui-arrow-sync-checkmark-20')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-arrow-sync-checkmark-20-o')
                        ->modalHeading('Setujui Proyek')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui proyek ini?')
                        ->modalSubmitActionLabel('Ya, Setujui')
                        ->modalCancelActionLabel('Batal')
                        ->visible(fn(Model $record): bool => $record->status === 'pending')
                        ->action(fn(Model $record) => $record->update(['status' => 'approved'])),

                    Tables\Actions\Action::make('reject')
                        ->label('Tolak Proyek')
                        ->icon('fluentui-arrow-sync-dismiss-20')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-arrow-sync-dismiss-20-o')
                        ->modalHeading('Tolak Proyek')
                        ->modalDescription('Apakah Anda yakin ingin menolak proyek ini?')
                        ->modalSubmitActionLabel('Ya, Tolak')
                        ->modalCancelActionLabel('Batal')
                        ->visible(fn(Model $record): bool => $record->status === 'pending')
                        ->action(fn(Model $record) => $record->update(['status' => 'rejected'])),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan')
                    ->icon('fluentui-archive-settings-20')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Massal')
                        ->icon('fluentui-cloud-dismiss-20')
                        ->color('danger')
                        ->modalIcon('fluentui-cloud-dismiss-20-o'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-cloud-off-20')
                        ->color('danger')
                        ->modalIcon('fluentui-cloud-off-20-o'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Kembalikan Massal')
                        ->icon('fluentui-cloud-sync-20')
                        ->color('warning')
                        ->modalIcon('fluentui-cloud-sync-20-o'),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan Massal')
                    ->icon('fluentui-archive-settings-20'),
            ])
            ->emptyStateHeading('Belum ada proyek')
            ->emptyStateDescription('Mulai dengan membuat proyek baru.')
            ->emptyStateIcon('fluentui-cloud-archive-20-o')
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
            'index' => Pages\ListProjects::route('/'),
            'CreateProject' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
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
            $pendingCount >= 10 => 'danger',    // Jika ada 10+ proyek pending
            $pendingCount >= 5 => 'warning',    // Jika ada 5+ proyek pending
            default => 'primary',               // Jika kurang dari 5 pending
        };
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        return "Proyek Menunggu Persetujuan: {$pendingCount}";
    }
}
