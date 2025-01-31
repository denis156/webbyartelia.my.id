<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Proyek')
                ->icon('fluentui-cloud-archive-20'),

            'pending' => Tab::make('Menunggu')
                ->icon('fluentui-arrow-sync-circle-20')
                ->badge(fn () => \App\Models\Project::where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),

            'approved' => Tab::make('Disetujui')
                ->icon('fluentui-checkmark-circle-20')
                ->badge(fn () => \App\Models\Project::where('status', 'approved')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),

            'rejected' => Tab::make('Ditolak')
                ->icon('fluentui-dismiss-circle-20')
                ->badge(fn () => \App\Models\Project::where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),

            'in_progress' => Tab::make('Dalam Pengerjaan')
                ->icon('fluentui-arrow-clockwise-20')
                ->badge(fn () => \App\Models\Project::where('status', 'in_progress')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress')),

            'completed' => Tab::make('Selesai')
                ->icon('fluentui-flag-20')
                ->badge(fn () => \App\Models\Project::where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),

            'cancelled' => Tab::make('Dibatalkan')
                ->icon('fluentui-dismiss-square-20')
                ->badge(fn () => \App\Models\Project::where('status', 'cancelled')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make()
    //             ->label('Buat Proyek Baru')
    //             ->icon('fluentui-cloud-add-20'),
    //     ];
    // }
}
