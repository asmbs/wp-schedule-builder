<?php

namespace ASMBS\ScheduleBuilder;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Loader
{
    public static $root;

    /**
     * Wire up the plugin.
     */
    public function __construct()
    {
        self::$root = dirname(dirname(__FILE__));

        // Load post types
        PostType\Session::load();
        PostType\Speaker::load();
        PostType\ResearchAbstract::load();
        PostType\Author::load();

        // Load extensions
        Extension\Acf::load();
    }
}
