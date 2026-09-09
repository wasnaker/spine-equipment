<?php

declare(strict_types=1);

namespace Modules\Equipment\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Equipment\Listeners\LogEquipmentActivity;

class EquipmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Event::listen(\Spine\Events\EntityCreated::class, LogEquipmentActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogEquipmentActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogEquipmentActivity::class . '@deleted');
    }
}
