<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\ValueConverter;

use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class AgendaItemTypeConverter implements ValueConverterInterface
{
    /**
     * Takes an item type ("Talk", "Abstract", etc.) and translates it into the corresponding
     * layout identifier ("item_talk", "item_abstract", etc.).
     *
     * @param   mixed  $input
     * @return  string
     */
    public function convert($input)
    {
        return sprintf('item_%s', strtolower($input));
    }
}
