<?php

namespace ASMBS\ScheduleBuilder\Taxonomy;

use ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ResearchAbstractKeyword extends AbstractTaxonomy
{
    const SLUG = 'keyword';

    public function getSingularLabel()
    {
        return 'Keyword';
    }

    public function getPluralLabel()
    {
        return 'Keywords';
    }

    public function getArgs()
    {
        return [
            'show_admin_column'  => false,
            'show_in_quick_edit' => false,
            'meta_box_cb'        => false,
        ];
    }

    public function getPostTypes()
    {
        return [
            PostType\ResearchAbstract::SLUG,
        ];
    }

}
