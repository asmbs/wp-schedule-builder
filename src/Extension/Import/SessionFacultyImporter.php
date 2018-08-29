<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CommaSplitter;
use ASMBS\ScheduleBuilder\Extension\Import\Writer\SessionFacultyWriter;
use ASMBS\ScheduleBuilder\PostType\Session;
use Port\Reader;
use Port\Steps\Step\ValueConverterStep;
use Port\Steps\StepAggregator as Workflow;

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
        return [
            'session_id',
            'label',
            'person_ids',
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Build the session faculty importer workflow.
     *
     * @param   Reader  $reader
     * @return  Workflow
     */
    public function buildWorkflow(Reader $reader)
    {
        $workflow = new Workflow($reader, $this->getPageTitle());

        $step = new ValueConverterStep();
        $step->add('person_ids', [CommaSplitter::class, 'convert']);
        $workflow->addStep($step);

        $workflow->addWriter(new SessionFacultyWriter($this, true, false));
        
        return $workflow;
    }
}
