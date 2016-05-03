<?php

namespace ASMBS\ScheduleBuilder\Taxonomy;

/**
 * A comprehensive base class for custom taxonomies.
 *
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class AbstractTaxonomy implements TaxonomyInterface
{
    /**
     * {@inheritdoc}
     */
    public static function load()
    {
        return new static();
    }

    /**
     * Constructor; registers hooks to initialize the taxonomy.
     */
    protected function __construct()
    {
        add_action('init', [$this, 'register']);
        add_action('sb/activate', [$this, 'register']);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Combine specified arguments with a full default list.
     */
    final protected function processArgs()
    {
        $singular = $this->getSingularLabel();
        $plural = $this->getPluralLabel();

        $singularSlug = sanitize_title($singular);

        $defaultArgs = [
            'label'              => $plural,
            'labels'             => [
                'name'                       => $plural,
                'singular_name'              => $singular,
                'menu_name'                  => $plural,
                'all_items'                  => sprintf('All %s', $plural),
                'edit_item'                  => sprintf('Edit %s', $singular),
                'view_item'                  => sprintf('View %s', $singular),
                'update_item'                => sprintf('Update %s', $singular),
                'add_new_item'               => sprintf('Add New %s', $singular),
                'new_item_name'              => sprintf('New %s Name', $singular),
                'parent_item'                => sprintf('Parent %s', $singular),
                'parent_item_colon'          => sprintf('Parent %s:', $singular),
                'search_items'               => sprintf('Search %s', $plural),
                'popular_items'              => sprintf('Popular %s', $plural),
                'separate_items_with_commas' => sprintf('Separate %s with commas.', strtolower($plural)),
                'add_or_remove_items'        => sprintf('Add or Remove %s', strtolower($plural)),
                'choose_from_most_used'      => sprintf('Choose from the most used %s', strtolower($plural)),
                'not_found'                  => sprintf('No %s found.', strtolower($plural)),
            ],
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_tagcloud'      => false,
            'show_in_quick_edit' => true,
            'meta_box_cb'        => null,
            'show_admin_column'  => false,
            'description'        => '',
            'hierarchical'       => false,
            'query_var'          => self::SLUG,
            'rewrite'            => ['slug' => $singularSlug, 'with_front' => true],
            'capabilities'       => [
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ],
            'sort'               => true,
        ];

        return array_replace_recursive($defaultArgs, (array) $this->getArgs());
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        register_taxonomy(static::SLUG, $this->getPostTypes(), $this->processArgs());
        foreach ($this->getPostTypes() as $postType) {
            register_taxonomy_for_object_type(static::SLUG, $postType);
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    final public static function exists()
    {
        return taxonomy_exists(static::SLUG);
    }

    /**
     * {@inheritdoc}
     */
    final public static function getDefinition()
    {
        return get_taxonomy(static::SLUG);
    }

    /**
     * {@inheritdoc}
     */
    final public static function getLabels()
    {
        return get_taxonomy_labels(static::getDefinition());
    }

    /**
     * {@inheritdoc}
     */
    final public static function getArchiveLink($term)
    {
        return get_term_link($term, static::SLUG);
    }
}
