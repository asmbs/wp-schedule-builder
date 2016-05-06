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
        add_filter('acf/load_field/key=embargo--date', [$this, 'loadDateChoices']);
        add_filter('acf/load_field/key=location--venue', [$this, 'loadVenueChoices']);
        add_filter('acf/load_field/key=credits--types', [$this, 'loadCreditChoices']);

        // Register AJAX actions
        add_action('wp_ajax_sb/load_rooms', [$this, 'loadAvailableRoomChoices']);

        self::$loaded = true;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Load ACF extension scripts.
     *
     * @param  string  $hook
     */
    public function enqueueScripts($hook)
    {
        wp_enqueue_script('sb/acf_js', AssetManager::getUrl('scripts/acf.min.js'), [
            'sb/main_js',
            'acf-input',
            'acf-pro-input',
        ], null);
        wp_localize_script('sb/acf_js', 'sb_acf', [
            'nonce' => wp_create_nonce('sb/acf_js'),
            'post' => isset($_REQUEST['post']) ? $_REQUEST['post'] : null,
        ]);
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

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Use the `logistics--dates` options field to populate the session date choices.
     *
     * @param   array  $field
     * @return  array
     */
    public function loadDateChoices($field)
    {
        if (isset($field['choices'])) {
            $choicesToAdd = [];
            while (have_rows('logistics--dates', 'sb_options')) {
                the_row();
                $choicesToAdd[get_sub_field('value')] = get_sub_field('label');
            }

            $field['choices'] = array_merge($field['choices'], $choicesToAdd);
        }

        return $field;
    }

    /**
     * Populate the venue list with the values of `logistics--locations`.
     *
     * @param   array  $field
     * @return  array
     */
    public function loadVenueChoices($field)
    {
        if (isset($field['choices'])) {
            $choicesToAdd = [];
            while (have_rows('logistics--locations', 'sb_options')) {
                the_row();
                $venue = get_sub_field('location_name');
                $choicesToAdd[$venue] = $venue;
            }

            $field['choices'] = array_merge($field['choices'], $choicesToAdd);
        }
        
        return $field;
    }

    /**
     * Repopulate the room list from the selected venue.
     *
     * Called via XHR.
     */
    public function loadAvailableRoomChoices()
    {
        $val = get_field('location--room', $_REQUEST['post']);

        $rooms = [];
        while (have_rows('logistics--locations', 'sb_options')) {
            the_row();
            $location = get_sub_field('location_name');
            if ($location == $_REQUEST['venue']) {
                while (have_rows('location_rooms')) {
                    the_row();
                    $name = get_sub_field('room_name');
                    $rooms[] = [
                        'name' => $name,
                        'selected' => ($name == $val),
                        'val' => $val,
                        'id' => $_REQUEST['post'],
                    ];
                }
            }
        }

        echo json_encode($rooms);

        exit;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Load the available credit choices from the schedule settings.
     *
     * @param   array  $field
     * @return  array
     */
    public function loadCreditChoices($field)
    {
        if (isset($field['choices'])) {
            $credits = [];
            while (have_rows('accreditation--types', 'sb_options')) {
                the_row();
                $type = get_sub_field('type');
                $credits[$type] = $type;
            }

            $field['choices'] = array_merge($field['choices'], $credits);
        }

        return $field;
    }
}
