<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class CredentialsNormalizer implements ConverterInterface {
    /**
     * Normalize a credential list.
     *
     * @param string $input
     *
     * @return  string
     */
    static public function convert( $input ) {
        return preg_replace( '/(?:,\s*|\s+)/i', ' ', $input );
    }
}
