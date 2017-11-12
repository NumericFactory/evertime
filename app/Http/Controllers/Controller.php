<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    function __construct() {
        $locale = app()->config->get('app.fallback_locale');
        if(auth()->check()){
            if(array_key_exists(auth()->user()->locale, config('app.locales'))){
                $locale = auth()->user()->locale;
            }
        }
        // Set Locale
        app()->setLocale($locale);
    }
}
