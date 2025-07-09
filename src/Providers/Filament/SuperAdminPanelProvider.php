<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\SuperAdmin\Pages\MainDashboard;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('super-admin')
            ->path('super-admin')
            ->authGuard('super-admin')
            ->login()
            ->colors([
                'gray' => Color::Slate,
                'primary' => Color::Rose,
            ])
            ->navigationGroups([
                'Customers',
                'Revenue',
                'Product Management',
                'Insights',
                'Settings',
                'Announcements',
                'Blog',
                'Roadmap',
            ])
            ->discoverResources(in: __DIR__.'/../../Filament/SuperAdmin/Resources', for: 'App\\Filament\\SuperAdmin\\Resources')
            ->discoverPages(in: __DIR__.'/../../Filament/SuperAdmin/Pages', for: 'App\\Filament\\SuperAdmin\\Pages')
            ->pages([
                MainDashboard::class,
            ])
            ->navigationItems([
                //
            ])
            ->discoverWidgets(in: __DIR__.'/../../Filament/SuperAdmin/Widgets', for: 'App\\Filament\\SuperAdmin\\Widgets')
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
            ]);
    }
}
