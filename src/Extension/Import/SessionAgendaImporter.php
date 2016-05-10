<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\PostType\Session;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Workflow;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionAgendaImporter extends AbstractImporter
{
    const SLUG = 'session_agenda_importer';

    public function getMenuTitle()
    {
        return 'Import Session Agendas';
    }

    public function getPageTitle()
    {
        return 'Session Agenda Importer';
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
     * Build the session agenda importer workflow.
     * 
     * @param   ReaderInterface  $reader
     * @return  Workflow
     */
    public function buildWorkflow(ReaderInterface $reader)
    {
        return new Workflow($reader, null, $this->getPageTitle());
    }
}
