<?php

namespace ASMBS\ScheduleBuilder\Model\Helper;

use ASMBS\ScheduleBuilder\Model\Person;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class FacultyGroup {
    /** @var  string */
    protected $label;

    /** @var  Person[] */
    protected $people = [];

    public function __construct( $label, $people ) {
        $this->label = $label;

        $people       = is_array( $people ) ? $people : [];
        $this->people = array_map( [ $this, 'loadPerson' ], $people );
    }

    /**
     * Wrap the given post into a Person entity.
     *
     * @param int|\WP_Post $post
     *
     * @return  Person
     */
    protected function loadPerson( $post ) {
        return new Person( $post );
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return  string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return  Person[]
     */
    public function getPeople() {
        return $this->people;
    }
}
