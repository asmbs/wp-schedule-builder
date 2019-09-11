<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class CommaSplitter implements ConverterInterface {
    /**
     * Split comma-separated values.
     *
     * @param string $input
     *
     * @return  string[]
     */
    public static function convert( $input ) {
        return $input ? preg_split( '/,\s*/i', $input ) : [];
    }
}
