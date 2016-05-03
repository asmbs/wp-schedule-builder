<?php

namespace ASMBS\ScheduleBuilder\Extension;

use ASMBS\ScheduleBuilder\AssetManager;
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

    /**
     * Load the extension.
     *
     * @return  Acf
     */
    public static function load()
    {
        return new self();
    }

    /**
     * Extension constructor.
     */
    protected function __construct()
    {
        // Register plugin options page
        add_action('init', [$this, 'registerOptionsPage']);

        // Enqueue scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);

        // Register JSON read locations
        add_filter('acf/settings/load_json', [$this, 'addLoadPaths']);

        // Register dynamic data hooks
        add_filter('acf/load_field/key=scheduling--date', [$this, 'loadDateChoices']);
        add_filter('acf/load_field/key=location--venue', [$this, 'loadVenueChoices']);
        // add_filter('acf/load_field/key=location--room', [$this, 'loadRoomChoices']);

        // Register AJAX actions
        add_action('wp_ajax_sb/load_rooms', [$this, 'loadAvailableRoomChoices']);

        self::$loaded = true;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Load ACF extension scripts
     */
    public function enqueueScripts()
    {
        wp_enqueue_script('sb/acf_js', AssetManager::getUrl('scripts/acf.min.js'), ['sb/main_js'], null);
        wp_localize_script('sb/acf_js', 'sb_acf', ['nonce' => wp_create_nonce('sb/acf_js')]);
    }

    /**
     * Register the plugin options page.
     */
    public function registerOptionsPage()
    {
        acf_add_options_page([
            'page_title' => 'Schedule Builder Settings',
            'menu_title' => 'Schedule Settings',
            'menu_slug'  => 'sb_options',
            'post_id'    => 'sb_options',
            'capability' => 'manage_options',
            'position'   => 32,
            'icon_url'   => 'dashicons-admin-generic',
        ]);
    }

    /**
     * Regiser JSON load paths for the plugin.
     *
     * @param   array  $paths
     * @return  array
     */
    public function addLoadPaths($paths)
    {
        $paths[] = Loader::$root .'/resources/acf';
        foreach (glob(Loader::$root .'/resources/acf/*/') as $dir) {
            $paths[] = $dir;
        }

        return $paths;
    }

    /**
     * Use the `event_details--dates` options field to populate the session date choices.
     *
     * @param   array  $field
     * @return  array
     */
    public function loadDateChoices($field)
    {
        if (isset($field['choices'])) {
            $choicesToAdd = [];
            while (have_rows('event_details--dates', 'sb_options')) {
                the_row();
                $choicesToAdd[get_sub_field('value')] = get_sub_field('label');
            }

            $field['choices'] = array_merge($field['choices'], $choicesToAdd);
        }

        return $field;
    }

    /**
     * Populate the venue list with the values of `event_details--locations`.
     *
     * @param   array  $field
     * @return  array
     */
    public function loadVenueChoices($field)
    {
        if (isset($field['choices'])) {
            $choicesToAdd = [];
            while (have_rows('event_details--locations', 'sb_options')) {
                the_row();
                $venue = get_sub_field('location_name');
                $choicesToAdd[$venue] = $venue;
            }

            $field['choices'] = array_merge($field['choices'], $choicesToAdd);
        }
        
        return $field;
    }

    public function loadAvailableRoomChoices()
    {
        $venues = get_field('event_details--locations', 'sb_options');
        $rooms = [];
        while (have_rows('event_details--locations', 'sb_options')) {
            the_row();
            $location = get_sub_field('location_name');
            if ($location == $_REQUEST['venue']) {
                while (have_rows('location_rooms')) {
                    the_row();
                    $rooms[] = ['name' => get_sub_field('room_name')];
                }
            }
        }

        echo json_encode($rooms);

        exit;
    }
}
