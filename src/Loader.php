<?php

namespace ASMBS\ScheduleBuilder;

use ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Loader
{
    private $root;

    /**
     * Wire up the plugin.
     */
    public function __construct()
    {
        $this->root = dirname(dirname(__FILE__));

        // Load post types
        PostType\Session::load();
    }
}
