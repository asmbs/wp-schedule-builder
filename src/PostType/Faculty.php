<?php

namespace ASMBS\ScheduleBuilder\PostType;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class Faculty extends AbstractPostType
{
    public function getArgs()
    {
        return [
            'supports' => ['author', 'revisions'],
        ];
    }
}
