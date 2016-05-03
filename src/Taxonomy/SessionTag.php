<?php

namespace ASMBS\ScheduleBuilder\Taxonomy;

use ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionTag extends AbstractTaxonomy
{
    const SLUG = 'session-tag';

    public function getSingularLabel()
    {
        return 'Session Tag';
    }

    public function getPluralLabel()
    {
        return 'Session Tags';
    }

    public function getArgs()
    {
        return [
            'show_in_quick_edit' => false,
            'meta_box_cb'        => false, // Handled by ACF
        ];
    }

    public function getPostTypes()
    {
        return [
            PostType\Session::SLUG,
        ];
    }
}
