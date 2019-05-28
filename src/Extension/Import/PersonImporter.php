<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use ASMBS\ScheduleBuilder\Extension\Import\Writer\PersonWriter;
use ASMBS\ScheduleBuilder\PostType\Person;
use Port\Steps\StepAggregator as Workflow;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 * @author  James Osterhout <jrosterhout@gmail.com>
 */
class PersonImporter extends FacultyImporter {
    const SLUG = 'person_importer';

    public function getMenuTitle() {
        return 'Import People';
    }

    public function getPageTitle() {
        return 'Person Importer';
    }

    public function getPostType() {
        return Person::SLUG;
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function setWriter( Workflow $workflow ) {
        $workflow->addWriter( new PersonWriter( $this, $this->replace ) );

        return $this;
    }
}
