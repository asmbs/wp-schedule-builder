<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;

use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class CredentialsNormalizer implements ValueConverterInterface
{
    /**
     * Normalize a credential list.
     * 
     * @param   string  $input
     * @return  string
     */
    public function convert($input)
    {
        return preg_replace('/(?:,\s*|\s+)/i', ' ', $input);
    }
}
