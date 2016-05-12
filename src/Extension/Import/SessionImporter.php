<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CommaSplitter;
use ASMBS\ScheduleBuilder\PostType\Session;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Writer\CallbackWriter;

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
            'content',
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

        // Add value converters
        $commaSplitter = new CommaSplitter();
        $workflow->addValueConverter('credit_types', $commaSplitter)
            ->addValueConverter('societies', $commaSplitter)
            ->addValueConverter('tags', $commaSplitter);
        
        // Add writer
        $workflow->addWriter(new CallbackWriter(function($row) use ($self) {
            $self->addNotice('<pre>'. print_r($row, true) .'</pre>');
        }));

        return $workflow;
    }
}
