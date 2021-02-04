<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use DB;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
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
            $sql     = str_replace("?", "'%s'", $query->sql);
            $sql     = vsprintf($sql, $query->bindings);

            $file    = storage_path('logs/sql/sql.log');
            $channel = config('logging.default');
            $level   = Logger::DEBUG;
            if ($channel == 'single') {
                //保存日志到单个文件中
                (new Logger('single'))->pushHandler(new StreamHandler($file, $level))->info($sql);
            }
            else
            {
                //日志滚动默认保存七天
                (new Logger('daily'))->pushHandler(new RotatingFileHandler($file, 7, $level))->info($sql);
            }
        });
    }
}
