<?php

namespace Chatter\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Route;
use Chatter\Core\Scopes\TenantScope;
trait Multitenant
{

    public static function bootMultitenant()
    {
        
        // if (auth()->check()) {

        //     $url = url()->current();

        //     if (strpos($url, '/admin')) {

        //         // if (auth()->user()->role_id != 1) {

        if (!config('chatter.multitenant')) {
            return ;
        }
        static::creating(function ($model) {
            if(session('tenant')){
                $model->tenant_id = session('tenant')->id;
            }
        });

        static::addGlobalScope(new TenantScope);

           
    }


}