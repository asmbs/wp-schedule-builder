<?php

namespace ASMBS\ScheduleBuilder\Taxonomy;

use ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ResearchAbstractType extends AbstractTaxonomy {
    const SLUG = 'abstract-type';

    public function getSingularLabel() {
        return 'Abstract Type';
    }

    public function getPluralLabel() {
        return 'Abstract Types';
    }

    public function getArgs() {
        return [
            'show_admin_column'  => true,
            'show_in_quick_edit' => false,
            'meta_box_cb'        => false,
        ];
    }

    public function getPostTypes() {
        return [
            PostType\ResearchAbstract::SLUG,
        ];
    }
}
