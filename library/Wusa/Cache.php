<?php
namespace Wusa;
use Zend\Cache\StorageFactory;
/**
 *
 * @author Lukas Plattner
 */
class Cache extends Configurable
{
    /**
     * Deletes Characters that are not allowed for Cacheids
     *
     * @param string $id
     * @return string
     */
    public static function sanitizeId($id)
    {
        return preg_replace('#[^a-zA-Z0-9_]#', '_', $id);
    }

    /**
     * @param string $cacheconfig
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public static function factory($cacheconfig = null)
    {
        $cacheconfig = (string)$cacheconfig;
        if(!$cacheconfig)
        {
            $cacheconfig = Config::getInstance()->system->cache->defaultconfig;
        }

        $cfg = self::getConfig($cacheconfig);
        return StorageFactory::factory($cfg->toArray());
    }
}
