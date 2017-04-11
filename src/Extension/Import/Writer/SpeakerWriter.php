<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Person;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SpeakerWriter extends FacultyWriter
{
    public function getPostType()
    {
        return Person::SLUG;
    }

    protected function getIDKey()
    {
        return 'person_id';
    }
}
