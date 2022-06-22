<?php

namespace Caritech\LaravelFileHandler;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Caritech\LaravelFileHandler\Skeleton\SkeletonClass
 */
class LaravelFileHandlerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-file-handler';
    }
}
