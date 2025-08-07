<?php

if (!function_exists('cxl_asset')) {
    function cxl_asset($path)
    {
        $timestamp = \Carbon\Carbon::now()->timestamp;
        $secure = config('app.force_ssl');

        $path = asset($path, $secure) . '?v=';
        $path .= config('app.debug') ? $timestamp : config('laravel-service-repository.version');

        return $path;
    }
}