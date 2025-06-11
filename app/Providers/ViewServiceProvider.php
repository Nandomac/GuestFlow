<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('components.sidebar', function ($view) {
            $user = User::find(Auth::id());

            if (!$user) {
                $view->with('menus', []);
                return;
            }

            if ($user->hasRole('SuperAdmin')) {
                $menus = Menu::with(['parent', 'childs'])->where('parent_id', null)->orderBy('ordering')->get();
            } else {
                $menus = Menu::with(['parent', 'childs'])->where('parent_id', null)
                    ->whereIn('permission', $user->getAllPermissions()
                        ->pluck('name')
                        ->toArray())
                    ->orderBy('ordering')->get();
            }

            $view->with('menus', $menus);
        });
    }
}
