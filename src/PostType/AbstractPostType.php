<?php

namespace ASMBS\ScheduleBuilder\PostType;

/**
 * A comprehensive base class for custom post types.
 * 
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class AbstractPostType implements PostTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public static function load()
    {
        return new static();
    }

    /**
     * Constructor; registers hooks to initialize the post type.
     */
    protected function __construct()
    {
        add_action('init', [$this, 'register']);
        add_action('sb/activate', [$this, 'register']);

        add_filter('default_hidden_meta_boxes', [$this, 'filterDefaultHiddenMetaBoxes'], 25, 2);

        add_filter(sprintf('manage_edit-%s_columns', static::SLUG), [$this, 'addAdvancedDateColumn'], 100);
        add_filter(sprintf('manage_edit-%s_sortable_columns', static::SLUG), [$this, 'setAdvancedDateOrdering'], 100);
        add_action(sprintf('manage_%s_posts_custom_column', static::SLUG), [$this, 'renderAdvancedDateColumn'], 100);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Combine the instance-specific arg list with the default arg list.
     *
     * @return  array
     */
    final protected function processArgs()
    {
        $singular = $this->getSingularLabel();
        $plural = $this->getPluralLabel();

        $singularSlug = sanitize_title($singular);
        $pluralSlug = sanitize_title($plural);

        $defaultArgs = [
            'label'               => $plural,
            'labels'              => [
                'name'                => $plural,
                'singular_name'       => $singular,
                'menu_name'           => $plural,
                'menu_admin_bar'      => $singular,
                'all_items'           => sprintf('All %s', $plural),
                'add_new'             => sprintf('Add New %s', $singular),
                'add_new_item'        => sprintf('Add New %s', $singular),
                'edit_item'           => sprintf('Edit %s', $singular),
                'new_item'            => sprintf('New %s', $singular),
                'view_item'           => sprintf('View %s', $singular),
                'search_items'        => sprintf('Search %s', $plural),
                'not_found'           => sprintf('No %s found', $plural),
                'not_found_in_trash'  => sprintf('No %s found in Trash', $plural),
                'parent_item'         => sprintf('Parent %s', $singular),
                'parent_item_colon'   => sprintf('Parent %s:', $singular),
            ],
            'public'              => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_in_menu'        => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-admin-post',
            'hierarchical'        => false,
            'has_archive'         => $pluralSlug,
            'rewrite'             => ['slug' => $singularSlug, 'with_front' => true],
            'supports'            => [],
            'can_export'          => true        ];

        return array_replace_recursive($defaultArgs, (array) $this->getArgs());
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    final public static function exists()
    {
        return post_type_exists(static::SLUG);
    }

    /**
     * {@inheritdoc}
     */
    final public static function getDefinition()
    {
        return get_post_type_object(static::SLUG);
    }

    /**
     * {@inheritdoc}
     */
    final public static function getLabels()
    {
        return get_post_type_labels(static::getDefinition());
    }

    /**
     * {@inheritdoc}
     */
    final public static function getArchiveLink()
    {
        return get_post_type_archive_link(static::SLUG);
    }

    /**
     * {@inheritdoc}
     */
    final public static function supports($feature)
    {
        return post_type_supports(static::SLUG, $feature);
    }

    /**
     * {@inheritdoc}
     */
    final public static function addSupport($feature)
    {
        add_post_type_support(static::SLUG, $feature);
    }

    /**
     * {@inheritdoc}
     */
    final public static function removeSupport($feature)
    {
        remove_post_type_support(static::SLUG, $feature);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Register the post type.
     */
    public function register()
    {
        register_post_type(static::SLUG, $this->processArgs());
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Set the meta boxes that are hidden by default.
     *
     * @param   array       $metaboxes
     * @param   \WP_Screen  $screen
     * @return  array
     */
    public function filterDefaultHiddenMetaBoxes(array $metaboxes, \WP_Screen $screen)
    {
        if ($screen->post_type === static::SLUG) {
            return $this->getDefaultHiddenMetaboxes();
        }

        return $metaboxes;
    }

    /**
     * List the default meta box IDs that should be hidden when editing a post of this post type.
     *
     * Override this method rather than {@see filterDefaultHiddenMetaBoxes} to quickly set your own.
     *
     * @see filterDefaultHiddenMetaboxes()
     *
     * @return  array
     */
    protected function getDefaultHiddenMetaboxes()
    {
        return [
            'authordiv',
            'commentsdiv',
            'commentstatusdiv',
            'postcustom',
            'revisionsdiv',
            'slugdiv',
            'trackbacksdiv'
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Add the "advanced date" column in place of the core date and author columns.
     *
     * @param   array  $columns
     * @return  array
     */
    public function addAdvancedDateColumn($columns)
    {
        unset($columns['author'], $columns['date']);
        $columns['date-adv'] = 'Last Modified';

        return $columns;
    }

    public function setAdvancedDateOrdering($columns)
    {
        $columns['date-adv'] = 'modified';

        return $columns;
    }

    public function renderAdvancedDateColumn($column)
    {
        $post = get_post();

        if ($column === 'date-adv') {
            // Get and print modified date
            $date = new \DateTime($post->post_modified);
            printf('<b>%s</b> %s', $date->format('m/d/Y'), $date->format('g:iA'));

            // Get and print author if applicable
            if (static::supports('author')) {
                $author = get_user_by('id', $post->post_author);
                printf('<br><span class="author">by %s</span>', $author->display_name);
            }
        }
    }
}
