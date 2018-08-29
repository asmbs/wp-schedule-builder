<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;
use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CommaSplitter;
use ASMBS\ScheduleBuilder\Extension\Import\Writer\ResearchAbstractWriter;
use ASMBS\ScheduleBuilder\PostType\ResearchAbstract;
use Port\Reader;
use Port\Steps\Step\ValueConverterStep;
use Port\Steps\StepAggregator as Workflow;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ResearchAbstractImporter extends AbstractImporter
{
    const SLUG = 'abstract_importer';

    public function getMenuTitle()
    {
        return 'Import Abstracts';
    }

    public function getPageTitle()
    {
        return 'Abstract Importer';
    }

    public function getPostType()
    {
        return ResearchAbstract::SLUG;
    }

    public function getColumns()
    {
        return [
            'abstract_id',
            'title',
            'author_ids',
            'introduction',
            'methods',
            'results',
            'conclusions',
            'embargo_date',
            'type',
            'societies',
            'keywords',
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

        // Add converters
        $step = new ValueConverterStep();
        $step->add('[author_ids]', [CommaSplitter::class, 'convert'])
            ->add('[societies]', [CommaSplitter::class, 'convert'])
            ->add('[keywords]', [CommaSplitter::class, 'convert']);
        $workflow->addStep($step);

        // Add writer
        $workflow->addWriter(new ResearchAbstractWriter($this, $this->replace));

        return $workflow;
    }
}
