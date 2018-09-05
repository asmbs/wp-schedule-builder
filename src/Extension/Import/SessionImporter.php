<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CommaSplitter;
use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\EvaluableConverter;
use ASMBS\ScheduleBuilder\Extension\Import\Writer\SessionWriter;
use ASMBS\ScheduleBuilder\PostType\Session;
use Port\Reader;
use Port\Steps\Step\ValueConverterStep;
use Port\Steps\StepAggregator as Workflow;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionImporter extends AbstractImporter
{
    const SLUG = 'session_importer';

    public function getMenuTitle()
    {
        return 'Import Sessions';
    }

    public function getPageTitle()
    {
        return 'Session Importer';
    }

    public function getPostType()
    {
        return Session::SLUG;
    }

    public function getColumns()
    {
        return [
            'session_id',
            'date',
            'start_time',
            'end_time',
            'title',
            'venue',
            'room',
            'credits_available',
            'credit_types',
            'session_type',
            'societies',
            'tags',
            'keywords',
            'content',
            'evaluable',
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Build the session import workflow.
     *
     * @param   Reader $reader
     * @return  Workflow
     */
    protected function buildWorkflow(Reader $reader)
    {
        $workflow = new Workflow($reader, $this->getPageTitle());

        // Add value converters
        $step = new ValueConverterStep();
        $step->add('[credit_types]', [CommaSplitter::class, 'convert'])
            ->add('[societies]', [CommaSplitter::class, 'convert'])
            ->add('[tags]', [CommaSplitter::class, 'convert'])
            ->add('[evaluable]', [EvaluableConverter::class, 'convert'])
            ->add('[keywords]', [CommaSplitter::class, 'convert']);
        $workflow->addStep($step);

        // Add writer
        $workflow->addWriter(new SessionWriter($this, $this->replace));

        return $workflow;
    }
}
