<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\PostType\Session;
use Ddeboer\DataImport\Workflow;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SpeakerImporter extends FacultyImporter
{
    const SLUG = 'speaker_importer';

    public function getMenuTitle()
    {
        return 'Import Speakers';
    }

    public function getPageTitle()
    {
        return 'Speaker Importer';
    }

    public function getPostType()
    {
        return Session::SLUG;
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function setWriter(Workflow $workflow)
    {
        $workflow->addWriter($this->getDebugWriter());

        return $this;
    }
}
