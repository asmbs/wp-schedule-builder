<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\PostType\Session;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Workflow;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionFacultyImporter extends AbstractImporter
{
    const SLUG = 'session_faculty_importer';

    public function getMenuTitle()
    {
        return 'Import Session Faculty';
    }

    public function getPageTitle()
    {
        return 'Session Faculty Importer';
    }

    public function getPostType()
    {
        return Session::SLUG;
    }

    public function getColumns()
    {
        return [];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Build the session faculty importer workflow.
     *
     * @param   ReaderInterface  $reader
     * @return  Workflow
     */
    public function buildWorkflow(ReaderInterface $reader)
    {
        return new Workflow($reader, null, $this->getPageTitle());
    }
}
