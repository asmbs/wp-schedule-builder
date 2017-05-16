<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;

use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;


/**
 * @author  James Osterhout jrosterhout@gmail.com
 */
class EvaluableConverter implements ValueConverterInterface
{
    /**
     * Takes an evaluable option (Yes, No, null) and translates it into the corresponding boolen value
     * for ACF true_false field type.
     *
     * @param   mixed  $input
     * @return  string
     */
    public function convert($input)
    {
        $input = strtolower($input);
        if ($input === 'yes' || $input === 1)
            return '1';
        else
            return '0';
    }
}
