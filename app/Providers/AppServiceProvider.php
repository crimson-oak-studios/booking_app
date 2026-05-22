<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::define('manage-business-data', fn ($user, $model = null) => $user->business_id && (!$model || !isset($model->business_id) || (int) $model->business_id === (int) $user->business_id));
    }
}
