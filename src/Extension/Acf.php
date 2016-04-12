<?php

namespace ASMBS\ScheduleBuilder\Extension;

use ASMBS\ScheduleBuilder\Loader;


/**
 * This extension adds support for ACF.
 *
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Acf
{
    /** @var  bool  Flag denoting whether the extension has been loaded. */
    protected static $loaded = false;

    public static function load()
    {
        return new self();
    }

    protected function __construct()
    {
        add_filter('acf/settings/load_json', [$this, 'addLoadLocation']);
        self::$loaded = true;
    }

    public function addLoadLocation($paths)
    {
        $paths[] = Loader::$root .'/resources/acf';
        foreach (glob(Loader::$root .'/resources/acf/*/') as $dir) {
            $paths[] = $dir;
        }

        return $paths;
    }
}
