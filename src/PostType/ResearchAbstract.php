<?php

namespace ASMBS\ScheduleBuilder\PostType;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ResearchAbstract extends AbstractPostType
{
    const SLUG = 'abstract';

    /**
     * {@inheritdoc}
     */
    public function getSingularLabel()
    {
        return 'Abstract';
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralLabel()
    {
        return 'Abstracts';
    }

    /**
     * {@inheritdoc}
     */
    public function getArgs()
    {
        return [
            'menu_position'   => 30,
            'menu_icon'       => 'dashicons-media-text',
            'has_archive'     => 'abstracts',
            'supports'        => ['author', 'revisions'],
            'capability_type' => ['abstract', 'abstracts'],
            'map_meta_cap'    => true,
        ];
    }
}
