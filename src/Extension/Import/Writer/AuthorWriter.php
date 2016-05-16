<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Author;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class AuthorWriter extends FacultyWriter
{
    public function getPostType()
    {
        return Author::SLUG;
    }

    protected function getIDKey()
    {
        return 'author_id';
    }
}
