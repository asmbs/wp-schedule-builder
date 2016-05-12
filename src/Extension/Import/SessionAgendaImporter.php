<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CommaSplitter;
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
        return [
            'session_id',
            'type',
            'start_time',
            'end_time',
            'talk_title',
            'speaker_id',
            'abstract_id',
            'presenter_id',
            'discussant_ids',
        ];
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
        $workflow = new Workflow($reader, null, $this->getPageTitle());

        $commaSplitter = new CommaSplitter();
        $workflow->addValueConverter('discussant_ids', $commaSplitter);
        
        return $workflow;
    }
}
