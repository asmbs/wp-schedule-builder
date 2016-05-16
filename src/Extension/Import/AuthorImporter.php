<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\Writer\AuthorWriter;
use ASMBS\ScheduleBuilder\PostType\ResearchAbstract;
use Ddeboer\DataImport\Workflow;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class AuthorImporter extends FacultyImporter
{
    const SLUG = 'author_importer';

    public function getMenuTitle()
    {
        return 'Import Authors';
    }

    public function getPageTitle()
    {
        return 'Author Importer';
    }

    public function getPostType()
    {
        return ResearchAbstract::SLUG;
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function setWriter(Workflow $workflow)
    {
        $workflow->addWriter(new AuthorWriter($this, $this->replace));

        return $this;
    }
}
