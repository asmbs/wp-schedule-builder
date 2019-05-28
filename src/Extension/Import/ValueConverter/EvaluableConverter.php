<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;

/**
 * @author  James Osterhout jrosterhout@gmail.com
 */
class EvaluableConverter implements ConverterInterface {
    /**
     * Takes an evaluable option (Yes, No, null) and translates it into the corresponding boolean value
     * for ACF true_false field type.
     *
     * @param mixed $input
     *
     * @return  string
     */
    public static function convert( $input ) {
        $input = strtolower( $input );
        if ( $input === 'yes' || $input === 1 ) {
            return '1';
        } else {
            return '0';
        }
    }
}
