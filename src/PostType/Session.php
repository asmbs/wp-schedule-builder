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
}
