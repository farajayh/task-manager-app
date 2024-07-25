<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;

use App\Models\User;
use App\Models\Task;

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
    public function boot(): void
    {
        //authorize update and deletion of tasks. Only the creator of a task can update or delete the task
        Gate::define('update-delete-task', function (User $user, Task $task) {
            return $user->id === $task->user_id;
        });
    }
}
