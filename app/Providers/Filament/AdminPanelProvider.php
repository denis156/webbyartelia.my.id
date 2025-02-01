<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\UserResource;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\ProjectResource;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'primary' => Color::hex('#154ba7'),
                'success' => Color::Green,
                'warning' => Color::Amber,
                'secondary' => Color::Gray,
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->slug('Profil')
                    ->setTitle('Profil Saya')
                    ->setIcon('heroicon-o-user')
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars',
                        rules: 'mimes:jpeg,png|max:1024'
                    )
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                function () {
                    return view('sidebar.bannerAdmin');
                }
            )
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make('Menu Utama')
                        ->items([
                            NavigationItem::make('Dashboard')
                                ->label(fn(): string => __('filament-panels::pages/dashboard.title'))
                                ->icon('heroicon-o-home')
                                ->url(fn(): string => Dashboard::getUrl())
                                ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.dashboard')),
                        ])
                        ->collapsible(false),

                    NavigationGroup::make('Manajemen Proyek')
                        ->items([
                            ...ProjectResource::getNavigationItems(),
                            ...InvoiceResource::getNavigationItems(),
                            ...PaymentResource::getNavigationItems(),
                        ]),

                    NavigationGroup::make('Manajemen Pengguna')
                        ->items([
                            ...UserResource::getNavigationItems(),
                        ]),

                    NavigationGroup::make('Pengaturan')
                        ->items([
                            NavigationItem::make('Profil')
                                ->icon('heroicon-o-user-circle')
                                ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.Profil'))
                                ->url(fn() => route('filament.admin.pages.Profil')),
                        ])
                        ->collapsible(false),
                ]);
            })
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
