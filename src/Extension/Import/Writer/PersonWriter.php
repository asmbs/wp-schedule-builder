<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Person;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 * @author  James Osterhout <jrosterhout@gmail.com>
 */
class PersonWriter extends FacultyWriter
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
