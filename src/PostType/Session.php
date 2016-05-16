<?php

namespace ASMBS\ScheduleBuilder\PostType;

use ASMBS\ScheduleBuilder\Taxonomy\SessionType;


/**
 * This post type represents any session in the conference program -- pre-conference courses, plenary sessions,
 * social events, etc., are all considered _sessions_.
 *
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Session extends AbstractPostType
{
    const SLUG = 'session';

    /**
     * {@inheritdoc}
     */
    public function getSingularLabel()
    {
        return 'Session';
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralLabel()
    {
        return 'Sessions';
    }

    /**
     * {@inheritdoc}
     */
    public function getArgs()
    {
        return [
            'labels'          => [
                'menu_name' => 'Schedule',
            ],
            'menu_position'   => 31,
            'menu_icon'       => 'dashicons-calendar-alt',
            'has_archive'     => 'schedule',
            'supports'        => ['title', 'editor', 'author', 'revisions'],
            'capability_type' => ['session', 'sessions'],
            'map_meta_cap'    => true,
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function __construct()
    {
        parent::__construct();

        // Customize post manager columns
        add_filter(sprintf('manage_edit-%s_columns', static::SLUG), [$this, 'setPostTableColumns']);
        add_filter(sprintf('manage_edit-%s_sortable_columns', static::SLUG), [$this, 'setSortableColumns']);
        add_action(sprintf('manage_%s_posts_custom_column', static::SLUG), [$this, 'renderColumn']);

        // Handle custom post manager ordering
        add_filter('posts_join_paged', [$this, 'getJoinSql'], 10, 2);
        add_filter('posts_orderby', [$this, 'getOrderBySql'], 10, 2);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Define the post manager column list.
     *
     * @param   array  $columns
     * @return  array
     */
    public function setPostTableColumns($columns)
    {
        $newColumns = [];
        foreach ($columns as $id => $title) {
            switch ($id) {
                case 'title':
                    $newColumns[$id] = $title;
                    $newColumns['datetime'] = 'Date/Time';
                    $newColumns['location'] = 'Location';
                    break;
                case 'taxonomy-'. SessionType::SLUG:
                    $labels = SessionType::getLabels();
                    $newColumns[$id] = $labels->singular_name;
                    break;
                default:
                    $newColumns[$id] = $title;
            }
        }

        return $newColumns;
    }

    /**
     * Add column sort designations.
     * 
     * @param   array  $columns
     * @return  array
     */
    public function setSortableColumns($columns)
    {
        $columns['datetime'] = 'datetime';

        return $columns;
    }

    /**
     * Render custom column content.
     *
     * @param  string  $column
     */
    public function renderColumn($column)
    {
        $post = get_post();

        switch ($column) {
            case 'datetime':
                $date = get_field('date');
                $start = get_field('start_time');
                $end = get_field('end_time');
                if ($date && $start && $end) {
                    $tz = new \DateTimeZone('America/Chicago');
                    $start = new \DateTime($date .' '. $start, $tz);
                    $end = new \DateTime($date .' '. $end, $tz);

                    printf(
                        '<b>%s</b><br>%s - %s',
                        $start->format('n/j (l)'),
                        $start->format('g:ia'),
                        $end->format('g:ia')
                    );
                } else {
                    printf('<b>%s</b>', 'TBA');
                }

                break;
            case 'location':
                $venue = get_field('venue');
                $room = get_field('room');
                
                printf(
                    '<b>%s</b><br>%s',
                    $venue == 'tba' ? 'Venue TBA' : $venue,
                    $room == 'tba' ? 'Room TBA' : $room
                );
                break;
        }
    }

    /**
     * Add JOIN clauses to the post table query for datetime ordering.
     *
     * @param   string     $sql
     * @param   \WP_Query  $query
     * @return  string
     */
    public function getJoinSql($sql, \WP_Query $query)
    {
        if (!$this->isPostListQuery($query)) {
            return $sql;
        }

        $orderby = $query->get('orderby');

        if ($orderby === 'datetime') {
            global $wpdb;
            $newSql = sprintf(
                'LEFT JOIN %1$s date_ ON date_.post_id = %2$s.ID AND date_.meta_key = "date" '
                .'LEFT JOIN %1$s start_ ON start_.post_id = %2$s.ID AND start_.meta_key = "start_time" '
                .'LEFT JOIN %1$s end_ ON end_.post_id = %2$s.ID AND end_.meta_key = "end_time"',
                $wpdb->postmeta,
                $wpdb->posts
            );

            return $newSql;
        }

        return $sql;
    }

    /**
     * Add ORDER BY clauses to the post table query for datetime ordering.
     *
     * @param   string     $sql
     * @param   \WP_Query  $query
     * @return  string
     */
    public function getOrderBySql($sql, \WP_Query $query)
    {
        if (!$this->isPostListQuery($query)) {
            return $sql;
        }

        $orderby = $query->get('orderby');
        $order = $query->get('order', 'ASC');

        if ($orderby === 'datetime') {
            global $wpdb;
            $newSql = sprintf(
                'STR_TO_DATE(CONCAT(date_.meta_value, " ", start_.meta_value), "%2$s %3$s") %1$s, '
                .'STR_TO_DATE(CONCAT(date_.meta_value, " ", end_.meta_value), "%2$s %3$s") %1$s, '
                .'post_title ASC',
                $order,
                '%Y/%m/%d',
                '%H:%s'
            );

            return $newSql;
        }

        return $sql;
    }
}
