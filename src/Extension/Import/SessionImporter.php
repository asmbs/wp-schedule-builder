<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

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

    /**
     * {@inheritdoc}
     */
    public function getMenuTitle()
    {
        return 'Import Sessions';
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTitle()
    {
        return 'Session Importer';
    }

    /**
     * {@inheritdoc}
     */
    public function getPostType()
    {
        return Session::SLUG;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return [
            'session_id',
            'title',
            'date',
            'start_time',
            'end_time',
            'venue',
            'room',
            'type',
            'societies',
            'tags',
            'credits',
            'credit_types',
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
