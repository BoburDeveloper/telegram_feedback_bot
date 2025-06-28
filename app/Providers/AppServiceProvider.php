<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::share('time', time());
        View::share('asset_theme', '/'.env('ASSET_FOLDER'));
    }

    public static function chat_type_detect($params) {
        extract($params);
        $is_group = false;
        $first_symbol = substr($chat['id'],0,1);
        $name = isset($chat['first_name']) ? $chat['first_name'] : '';
        $type = isset($chat['type']) ? $chat['type'] : '';
        if($first_symbol=='-') {
            $is_group = true;
            $name = 'group';
        }
        if(isset($chat['title'])) {
            $name = $chat['title'];
        }
        
        $result = [
            'name'=>$name,
            'type'=>$type,
            'is_gorup'=>$is_group,
            ];
        return $result;

    }
}
