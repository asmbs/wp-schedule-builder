<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\ValueConverter\CredentialsNormalizer;
use Port\Reader;
use Port\Steps\Step\ValueConverterStep;
use Port\Steps\StepAggregator as Workflow;

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
     * @param   Reader $reader
     * @return  Workflow
     */
    protected function buildWorkflow(Reader $reader)
    {
        $workflow = new Workflow($reader, $this->getPageTitle());

        $step = new ValueConverterStep();
        $step->add('[credentials]', [CredentialsNormalizer::class, 'convert']);
        $workflow->addStep($step);

        // Rely on the subclass to set the writer
        $this->setWriter($workflow);

        return $workflow;
    }

    /**
     * Set the type-specific writer.
     *
     * @param   Workflow $workflow
     * @return  $this
     */
    abstract protected function setWriter(Workflow $workflow);
}
