<?php

namespace ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Speaker extends Faculty
{
    const SLUG = 'speaker';

    public function getSingularLabel()
    {
        return 'Speaker';
    }

    public function getPluralLabel()
    {
        return 'Speakers';
    }

    public function getArgs()
    {
        return [
            'labels'          => [
                'all_items' => 'Speakers',
            ],
            'public'          => false,
            'show_in_menu'    => 'edit.php?post_type='. Session::SLUG,
            'has_archive'     => false,
            'supports'        => ['editor', 'revisions'],
            'capability_type' => ['speaker', 'speakers'],
            'map_meta_cap'    => true,
        ];
    }
}
