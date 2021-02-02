<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use DB;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        error_reporting(E_ALL ^ E_NOTICE);
        Schema::defaultStringLength(191);
        $this->logSql();
    }

    private function logSql()
    {
        DB::listen(function ($query) {
            $sql = str_replace("?", "'%s'", $query->sql);
            $log = vsprintf($sql, $query->bindings);
            //日志滚动默认保存七天
            (new Logger('log'))->pushHandler(new RotatingFileHandler(storage_path('logs/sql/sql.log'), 7))
                ->info($log);
        });
    }
}
