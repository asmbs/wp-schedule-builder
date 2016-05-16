<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;
use ASMBS\ScheduleBuilder\Extension\Import\Writer\ResearchAbstractWriter;
use ASMBS\ScheduleBuilder\PostType\ResearchAbstract;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Workflow;


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
     * @param   ReaderInterface  $reader
     * @return  Workflow
     */
    protected function buildWorkflow(ReaderInterface $reader)
    {
        $commaSplitter = new ValueConverter\CommaSplitter();

        $workflow = new Workflow($reader, null, $this->getPageTitle());

        // Add converters
        $workflow
            ->addValueConverter('author_ids', $commaSplitter)
            ->addValueConverter('societies', $commaSplitter)
            ->addValueConverter('keywords', $commaSplitter);

        // Add writer
        $workflow->addWriter(new ResearchAbstractWriter($this, $this->replace));

        return $workflow;
    }
}
