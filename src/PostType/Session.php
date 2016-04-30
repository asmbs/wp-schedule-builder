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
}
