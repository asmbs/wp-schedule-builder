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

        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param  string  $hook
     */
    public function enqueueAdminScripts($hook)
    {
        $baseUrl = plugin_dir_url(__DIR__ .'/../assets/dist') .'dist/';

        wp_enqueue_style('schedule_builder/admin_css', $baseUrl .'styles/admin.min.css');
    }
}
