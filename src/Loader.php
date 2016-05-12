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
        Extension\Import\SpeakerImporter::load();
        Extension\Import\ResearchAbstractImporter::load();
        Extension\Import\AuthorImporter::load();

        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueueAdminScripts()
    {
        wp_enqueue_style('sb/admin_css', AssetManager::getUrl('styles/admin.min.css'), [], null);
        wp_enqueue_script('sb/main_js', AssetManager::getUrl('scripts/main.min.js'), ['jquery'], null);
    }
}
