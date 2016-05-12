<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\PostType\ResearchAbstract;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Writer\CallbackWriter;


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
            'society',
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
        $self = $this;

        $workflow = new Workflow($reader, null, $this->getPageTitle());
        $workflow->addWriter(new CallbackWriter(function($row) use ($self) {
            $self->addNotice('<pre>'. print_r($row, true) .'</pre>');
        }));

        return $workflow;
    }
}
