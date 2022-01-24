<?php

namespace Level51\ThreeQ;

use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Cache\DefaultCacheFactory;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Injector\Injector;

class Util
{
    /**
     * @return Config_ForClass
     */
    public static function config(): Config_ForClass
    {
        return Config::forClass('Level51\ThreeQ');
    }

    /**
     * @return CacheInterface
     * @throws NotFoundExceptionInterface
     */
    public static function getCache(): CacheInterface
    {
        return Injector::inst()->get(DefaultCacheFactory::class)->create(self::class);
    }
}
