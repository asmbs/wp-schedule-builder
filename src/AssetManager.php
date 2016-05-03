<?php

namespace ASMBS\ScheduleBuilder;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class AssetManager
{
    /** @var  string */
    protected static $baseUri;

    /** Set the Base URI. */
    protected static function setBaseUri()
    {
        $json = file_get_contents(__DIR__ .'/../assets/manifest.json');
        $manifest = json_decode($json, true);

        if ($manifest && isset($manifest['paths']) && isset($manifest['paths']['dist'])) {
            self::$baseUri = plugin_dir_url(dirname(__FILE__)) . $manifest['paths']['dist'];
        }
    }

    /**
     * Returns a URL to a compiled asset file.
     * 
     * @param   string  $path
     * @return  string
     */
    public static function getUrl($path)
    {
        if (!self::$baseUri) {
            self::setBaseUri();
        }

        return self::$baseUri . $path;
    }
}
