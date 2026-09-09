<?php

declare(strict_types=1);

namespace Modules\Equipment\Providers;

use Illuminate\Support\ServiceProvider;

class EquipmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
