<?php

namespace ASMBS\ScheduleBuilder\PostType;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Author extends Faculty
{
    public function getSingularLabel()
    {
        return 'Author';
    }

    public function getPluralLabel()
    {
        return 'Authors';
    }

    public function getArgs()
    {
        return [
            'labels'          => [
                'all_items' => 'Authors',
            ],
            'public'          => false,
            'show_in_menu'    => 'edit.php?post_type='. ResearchAbstract::SLUG,
            'has_archive'     => false,
            'supports'        => ['editor', 'revisions'],
            'capability_type' => ['author', 'authors'],
            'map_meta_cap'    => true,
        ];
    }
}
