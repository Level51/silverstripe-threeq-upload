<?php

namespace Level51\ThreeQ;

use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Cache\DefaultCacheFactory;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\Core\Injector\Injector;

/**
 * Util class for generic helper methods.
 *
 * @package Level51\ThreeQ
 */
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
     *
     * @throws NotFoundExceptionInterface
     */
    public static function getCache(): CacheInterface
    {
        return Injector::inst()->get(DefaultCacheFactory::class)->create(self::class);
    }

    /**
     * Try to sanitize the given file type to a generic format.
     *
     * Input of e.g. 'video/mp4' or '.mp4' returns 'mp4'.
     *
     * @param $fileType
     *
     * @return string
     */
    public static function sanitizeFileType($fileType): string
    {
        if (strpos($fileType, '/') > -1) {
            $parts = explode('/', $fileType);
            $fileType = end($parts);
        }

        if ($fileType[0] === '.') {
            $fileType = substr($fileType, 1);
        }

        return $fileType;
    }
}
