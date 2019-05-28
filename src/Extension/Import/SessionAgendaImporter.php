<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\AgendaItemTypeConverter;
use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CommaSplitter;
use ASMBS\ScheduleBuilder\Extension\Import\Writer\SessionAgendaWriter;
use ASMBS\ScheduleBuilder\PostType\Session;
use Port\Reader;
use Port\Steps\Step\ValueConverterStep;
use Port\Steps\StepAggregator as Workflow;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionAgendaImporter extends AbstractImporter {
    const SLUG = 'session_agenda_importer';

    public function getMenuTitle() {
        return 'Import Session Agendas';
    }

    public function getPageTitle() {
        return 'Session Agenda Importer';
    }

    public function getPostType() {
        return Session::SLUG;
    }

    public function getColumns() {
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
     * @param Reader $reader
     *
     * @return  Workflow
     */
    public function buildWorkflow( Reader $reader ) {
        $workflow = new Workflow( $reader, $this->getPageTitle() );

        $step = new ValueConverterStep();
        $step->add( '[type]', [ AgendaItemTypeConverter::class, 'convert' ] )
             ->add( '[discussant_ids]', [ CommaSplitter::class, 'convert' ] );
        $workflow->addStep( $step );

        $workflow->addWriter( new SessionAgendaWriter( $this, true, false ) );

        return $workflow;
    }
}
