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
        // Register plugin options page
        add_action('init', [$this, 'registerOptionsPage']);

        // Register JSON read locations
        add_filter('acf/settings/load_json', [$this, 'addLoadPaths']);

        // Register dynamic data hooks
        add_filter('acf/load_field/key=scheduling--date', [$this, 'loadDateChoices']);

        self::$loaded = true;
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function registerOptionsPage()
    {
        acf_add_options_page([
            'page_title' => 'Schedule Builder Settings',
            'menu_title' => 'Schedule Settings',
            'menu_slug'  => 'sb_options',
            'post_id'    => 'sb_options',
            'capability' => 'manage_options',
            'position'   => 31,
            'icon_url'   => 'dashicons-admin-generic',
        ]);
    }

    public function addLoadPaths($paths)
    {
        $paths[] = Loader::$root .'/resources/acf';
        foreach (glob(Loader::$root .'/resources/acf/*/') as $dir) {
            $paths[] = $dir;
        }

        return $paths;
    }

    public function loadDateChoices($field)
    {
        if (isset($field['choices'])) {
            $choicesToAdd = [];
            while (have_rows('event_details/dates', 'sb_options')) {
                the_row();
                $choicesToAdd[get_sub_field('value')] = get_sub_field('label');
            }

            $field['choices'] = array_merge($field['choices'], $choicesToAdd);
        }

        return $field;
    }
}
