<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Speaker;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SpeakerWriter extends FacultyWriter
{
    public function getPostType()
    {
        return Speaker::SLUG;
    }

    protected function getIDKey()
    {
        return 'speaker_id';
    }
}
