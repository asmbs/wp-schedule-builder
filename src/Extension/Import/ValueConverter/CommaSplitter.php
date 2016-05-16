<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;

use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class CommaSplitter implements ValueConverterInterface
{
    /**
     * Split comma-separated values.
     *
     * @param   string  $input
     * @return  string[]
     */
    public function convert($input)
    {
        return preg_split('/,\s*/i', $input);
    }
}
