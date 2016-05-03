<?php

namespace ASMBS\ScheduleBuilder\PostType;


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

        add_filter(sprintf('manage_edit-%s_columns', self::SLUG), [$this, 'setPostTableColumns']);
        add_filter(sprintf('manage_edit-%s_sortable_columns', self::SLUG), [$this, 'setSortableColumns']);
        add_action(sprintf('manage_%s_posts_custom_column', self::SLUG), [$this, 'renderColumn']);
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
                    $newColumns['datetime'] = 'Date/Time';
                    $newColumns[$id] = $title;
                    $newColumns['location'] = 'Location';
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
}
