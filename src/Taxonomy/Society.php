<?php

namespace ASMBS\ScheduleBuilder\Taxonomy;

use ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Society extends AbstractTaxonomy
{
    const SLUG = 'society';

    public function getSingularLabel()
    {
        return 'Society';
    }

    public function getPluralLabel()
    {
        return 'Societies';
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
