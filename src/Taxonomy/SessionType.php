<?php

namespace ASMBS\ScheduleBuilder\Taxonomy;

use ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionType extends AbstractTaxonomy
{
    const SLUG = 'session-type';

    public function getSingularLabel()
    {
        return 'Session Type';
    }

    public function getPluralLabel()
    {
        return 'Session Types';
    }

    public function getArgs()
    {
        return [
            'show_admin_column'  => true,
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
