<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

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
        ];
    }
}
