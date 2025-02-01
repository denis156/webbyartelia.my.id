<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'fluentui-people-team-20-o';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        // Kolom Kiri
                        Group::make()
                            ->schema([
                                Section::make('Informasi Pengguna')
                                    ->description('Kelola informasi pribadi pengguna di sini.')
                                    ->icon('fluentui-person-info-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Masukkan nama lengkap')
                                            ->autocomplete('name'),

                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('contoh@email.com')
                                            ->autocomplete('email'),

                                        Forms\Components\Select::make('role')
                                            ->label('Peran')
                                            ->native(false)
                                            ->required()
                                            ->options([
                                                'admin' => 'Administrator',
                                                'client' => 'Klien'
                                            ])
                                            ->default('client')
                                            ->preload()
                                            ->searchable(),
                                    ]),

                                Section::make('Kontak & Alamat')
                                    ->description('Informasi kontak dan alamat pengguna.')
                                    ->icon('fluentui-call-exclamation-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('phone_number')
                                            ->label('Nomor Telepon')
                                            ->tel()
                                            ->maxLength(255)
                                            ->placeholder('Contoh: 081234567890'),

                                        Forms\Components\Textarea::make('address')
                                            ->label('Alamat Lengkap')
                                            ->rows(3)
                                            ->placeholder('Masukkan alamat lengkap'),
                                    ]),
                            ]),

                        // Kolom Kanan
                        Group::make()
                            ->schema([
                                Section::make('Keamanan')
                                    ->description('Pengaturan kata sandi pengguna.')
                                    ->icon('fluentui-lock-shield-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('password')
                                            ->label('Kata Sandi')
                                            ->password()
                                            ->dehydrateStateUsing(function ($state) {
                                                if (!$state) {
                                                    return null;
                                                }
                                                return Hash::make($state);
                                            })
                                            ->required(fn(string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn($state) => filled($state))
                                            ->maxLength(255)
                                            ->autocomplete('new-password')
                                            ->revealable(),
                                    ]),

                                Section::make('Pengaturan Profil')
                                    ->description('Pengaturan profil dan verifikasi tambahan.')
                                    ->icon('fluentui-video-person-20')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\FileUpload::make('avatar_url')
                                            ->label('Foto Profil')
                                            ->image()
                                            ->avatar()
                                            ->directory('avatars')
                                            ->visibility('public')
                                            ->maxSize(1024)
                                            ->imageEditor()
                                            ->circleCropper()
                                            ->helperText('Maksimal 1MB. Format: JPG, PNG, GIF'),

                                        Forms\Components\DateTimePicker::make('email_verified_at')
                                            ->label('Email Terverifikasi Pada')
                                            ->native(false)
                                            ->displayFormat('d M Y H:i')
                                            ->timezone('Asia/Jakarta'),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Status Aktif')
                                            ->required()
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('danger')
                                            ->onIcon('fluentui-eye-20')
                                            ->offIcon('fluentui-eye-off-20')
                                            ->helperText('Pengguna tidak aktif tidak dapat masuk ke sistem.'),
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
                    ->alignLeft()
                    ->weight(FontWeight::Bold),

                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Foto')
                    ->circular()
                    ->size(40)
                    ->alignCenter()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=111827'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->copyable()
                    ->alignCenter()
                    ->copyMessage('Nama berhasil disalin')
                    ->tooltip(fn(Model $record): string => "Dibuat pada: {$record->created_at->format('d M Y')}")
                    ->wrap(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->alignCenter()
                    ->copyMessage('Email berhasil disalin')
                    ->icon('fluentui-person-mail-20-o')
                    ->iconColor('primary')
                    ->description(fn(Model $record): string => $record->email_verified_at ? 'Terverifikasi' : 'Belum Terverifikasi')
                    ->wrap(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->alignCenter()
                    ->colors([
                        'warning' => 'client',
                        'success' => 'admin',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'admin' => 'Administrator',
                        'client' => 'Klien',
                        default => $state,
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('No. Telepon')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->alignCenter()
                    ->copyMessage('Nomor telepon berhasil disalin')
                    ->formatStateUsing(fn(?string $state): string => $state ?? 'Belum diisi')
                    ->icon('fluentui-person-call-20-o')
                    ->iconColor('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('fluentui-eye-20-o')
                    ->falseIcon('fluentui-eye-off-20-o')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->tooltip(fn(Model $record): string => $record->is_active ? 'Pengguna Aktif' : 'Pengguna Tidak Aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->toggleable()
                    ->alignCenter()
                    ->description(fn(Model $record) => $record->created_at->diffForHumans())
                    ->tooltip(
                        fn(Model $record): string =>
                        "Dibuat: {$record->created_at->format('d M Y H:i')}\n" .
                            "Diupdate: {$record->updated_at->format('d M Y H:i')}"
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Terhapus')
                    ->indicator('Terhapus'),

                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->native(false)
                    ->indicator('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif'
                    ]),

                Tables\Filters\Filter::make('verified')
                    ->label('Terverifikasi')
                    ->indicator('Terverifikasi')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at')),
            ])
            ->filtersFormColumns(1)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah Pengguna')
                        ->icon('fluentui-person-edit-20')
                        ->color('primary'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus Pengguna')
                        ->icon('fluentui-person-delete-20')
                        ->color('danger')
                        ->modalIcon('fluentui-person-delete-20-o')
                        ->requiresConfirmation(),

                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-person-prohibited-20')
                        ->color('danger')
                        ->modalIcon('fluentui-person-prohibited-20-o')
                        ->requiresConfirmation(),

                    Tables\Actions\RestoreAction::make()
                        ->label('Kembalikan Pengguna')
                        ->icon('fluentui-person-sync-20')
                        ->color('warning')
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('activate')
                        ->label('Aktivasi Pengguna')
                        ->icon('fluentui-eye-20')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aktivasi Pengguna')
                        ->modalDescription('Apakah Anda yakin ingin mengaktifkan pengguna ini?')
                        ->modalSubmitActionLabel('Ya, Aktifkan')
                        ->modalCancelActionLabel('Batal')
                        ->visible(fn(Model $record): bool => !$record->is_active)
                        ->action(fn(Model $record) => $record->update(['is_active' => true])),

                    Tables\Actions\Action::make('deactivate')
                        ->label('Nonaktifkan Pengguna')
                        ->icon('fluentui-eye-off-20')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalIcon('fluentui-eye-off-20-o')
                        ->modalHeading('Nonaktifkan Pengguna')
                        ->modalDescription('Apakah Anda yakin ingin menonaktifkan pengguna ini?')
                        ->modalSubmitActionLabel('Ya, Nonaktifkan')
                        ->modalCancelActionLabel('Batal')
                        ->visible(fn(Model $record): bool => $record->is_active)
                        ->action(fn(Model $record) => $record->update(['is_active' => false])),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan')
                    ->icon('fluentui-person-settings-20'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Massal')
                        ->icon('fluentui-people-team-delete-20')
                        ->color('danger')
                        ->modalIcon('fluentui-people-team-delete-20-o')
                        ->modalHeading('Hapus Pengguna Massal')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengguna yang dipilih?'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Selamanya')
                        ->icon('fluentui-people-prohibited-20')
                        ->color('danger')
                        ->modalIcon('fluentui-people-prohibited-20-o'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Kembalikan Massal')
                        ->icon('fluentui-people-sync-20')
                        ->color('warning')
                        ->modalIcon('fluentui-people-sync-20-o'),
                ])
                    ->button()
                    ->color('primary')
                    ->label('Tindakan Massal')
                    ->icon('fluentui-people-settings-20'),
            ])
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Mulai dengan membuat pengguna baru.')
            ->emptyStateIcon('fluentui-people-team-20-o')
            ->poll('30s')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //....
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
        return static::getModel()::where('is_active', true)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $userCount = static::getModel()::where('is_active', true)->count();

        return match (true) {
            $userCount >= 50 => 'warning',   // Jika user lebih dari 50
            $userCount >= 20 => 'info',      // Jika user lebih dari 20
            $userCount >= 10 => 'success',   // Jika user lebih dari 10
            default => 'primary',            // Jika user kurang dari 10
        };
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $totalUsers = static::getModel()::count();
        $activeUsers = static::getModel()::where('is_active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        return "Total Pengguna Aktif: {$activeUsers}\nPengguna Tidak Aktif: {$inactiveUsers}";
    }
}
