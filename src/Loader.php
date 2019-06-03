<?php

namespace ASMBS\ScheduleBuilder;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Loader {
    public static $root;

    /**
     * Wire up the plugin.
     */
    public function __construct() {
        self::$root = dirname( dirname( __FILE__ ) );

        // Load post types
        PostType\Session::load();
        PostType\ResearchAbstract::load();
        PostType\Person::load();

        // Load taxonomies
        Taxonomy\SessionTag::load();
        Taxonomy\SessionType::load();
        Taxonomy\Society::load();
        Taxonomy\ResearchAbstractType::load();
        Taxonomy\ResearchAbstractKeyword::load();

        // Load extensions
        Extension\Acf::load();
        Extension\Import\SessionImporter::load();
        Extension\Import\SessionFacultyImporter::load();
        Extension\Import\SessionAgendaImporter::load();
        Extension\Import\ResearchAbstractImporter::load();
        Extension\Import\PersonImporter::load();

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ] );

        // Register activation hook
        register_activation_hook( self::$root . '/schedule-builder.php', [ $this, 'activate' ] );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param $hook
     */
    public function enqueueAdminScripts( $hook ) {

        // Styles
        $plugindir = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'sb/admin_css', $plugindir . '../dist/styles/main.css', [], null );

        // Script for editing sessions
        $postType = get_post_type();
        if ( $postType === 'session' && $hook === 'post.php' ) {
            wp_enqueue_script( 'sb/main_js', $plugindir . '../dist/scripts/main.bundle.js', [
                'jquery',
                'acf-input',
                'acf-pro-input',
                'acf-field-group',
                'acf-pro-field-group',
                'select2'
            ], null, true );
        }
    }

    /**
     * Activation hook.
     */
    public function activate() {
        do_action( 'sb/activate' );
        flush_rewrite_rules();
    }
}
