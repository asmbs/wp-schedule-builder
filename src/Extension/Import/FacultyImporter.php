<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CredentialsNormalizer;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Workflow;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class FacultyImporter extends AbstractImporter
{
    public function getColumns()
    {
        return [
            'id',
            'prefix',
            'first',
            'mi',
            'last',
            'suffix',
            'credentials',
            'organization',
            'title',
            'city',
            'state',
            'country',
            'bio',
            'photo',
        ];
    }

    /**
     * @param   ReaderInterface  $reader
     * @return  Workflow
     */
    protected function buildWorkflow(ReaderInterface $reader)
    {
        $workflow = new Workflow($reader, null, $this->getPageTitle());

        $workflow->addValueConverter('credentials', new CredentialsNormalizer());

        // Rely on the subclass to set the writer
        $this->setWriter($workflow);

        return $workflow;
    }

    /**
     * Set the type-specific writer.
     *
     * @param   Workflow  $workflow
     * @return  $this
     */
    abstract protected function setWriter(Workflow $workflow);
}
