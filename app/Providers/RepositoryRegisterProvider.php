<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryRegisterProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Interface\UserInterface::class, \App\Repositories\UserRepository::class);
        $this->app->bind(\App\Interfaces\AuthSidebarMenuInterface::class, \App\Repositories\AuthSidebarMenuRepository::class);
        $this->app->bind(\App\Interfaces\CategoryInterface::class, \App\Repositories\CategoryRepository::class);
        $this->app->bind(\App\Interfaces\PostInterface::class, \App\Repositories\PostRepository::class);
        $this->app->bind(\App\Interfaces\VehicleInterface::class, \App\Repositories\VehicleRepository::class);
        $this->app->bind(\App\Interfaces\VehicleMakeModelInterface::class, \App\Repositories\VehicleMakeModelRepository::class);
        $this->app->bind(\App\Interfaces\VehicleTypeInterface::class, \App\Repositories\VehicleTypeRepository::class);
        $this->app->bind(\App\Interfaces\DriverDetailInterface::class, \App\Repositories\DriverDetailRepository::class);
        $this->app->bind(\App\Interfaces\DriverMateInterface::class, \App\Repositories\DriverMateRepository::class);
        $this->app->bind(\App\Interfaces\LicenseCategoryInterface::class, \App\Repositories\LicenseCategoryRepository::class);
        $this->app->bind(\App\Interfaces\RouteTripInterface::class, \App\Repositories\RouteTripRepository::class);
        $this->app->bind(\App\Interfaces\WaybillInterface::class, \App\Repositories\WaybillRepository::class);
        $this->app->bind(\App\Interfaces\VehicleMaintenanceInterface::class, \App\Repositories\VehicleMaintenanceRepository::class);
        $this->app->bind(\App\Interfaces\ImpoundmentInterface::class, \App\Repositories\ImpoundmentRepository::class);
        $this->app->bind(\App\Interfaces\VehicleBreakdownInterface::class, \App\Repositories\VehicleBreakdownRepository::class);
        $this->app->bind(\App\Interfaces\VehicleBoggedDownInterface::class, \App\Repositories\VehicleBoggedDownRepository::class);
        $this->app->bind(\App\Interfaces\IllegalDetentionInterface::class, \App\Repositories\IllegalDetentionRepository::class);
        $this->app->bind(\App\Interfaces\IllegalHaulageInterface::class, \App\Repositories\IllegalHaulageRepository::class);
        $this->app->bind(\App\Interfaces\IllegalShortageInterface::class, \App\Repositories\IllegalShortageRepository::class);
        $this->app->bind(\App\Interfaces\BreathaliserTestInterface::class, \App\Repositories\BreathaliserTestRepository::class);
        $this->app->bind(\App\Interfaces\AccidentInterface::class, \App\Repositories\AccidentRepository::class);
        $this->app->bind(\App\Interfaces\RouteDiversionInterface::class, \App\Repositories\RouteDiversionRepository::class);
        $this->app->bind(\App\Interfaces\DriverMisconductInterface::class, \App\Repositories\DriverMisconductRepository::class);
        $this->app->bind(\App\Interfaces\GoodsInspectionInterface::class, \App\Repositories\GoodsInspectionRepository::class);
        $this->app->bind(\App\Interfaces\WaybillFileInterface::class, \App\Repositories\WaybillFileRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
