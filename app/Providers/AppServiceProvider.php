<?php

namespace App\Providers;

use App\Forms\Components\PajakInput;
use App\Forms\Components\RupiahInput;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\TextInput;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        TextInput::macro('rupiah', function (bool $isReadOnly = false, bool $isLive = false) {
            /** @var TextInput $this */
            $name = $this->getName();
            return RupiahInput::make($name)->rupiah($isReadOnly, $isLive);
        });

        TextInput::macro('pajak', function () {
            /** @var TextInput $this */
            $name = $this->getName();
            return PajakInput::make($name)->pajak();
        });
    }
}
