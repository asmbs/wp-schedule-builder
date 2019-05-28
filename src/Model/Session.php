<?php

namespace ASMBS\ScheduleBuilder\Model;

use ASMBS\ScheduleBuilder\Model\Helper;
use ASMBS\ScheduleBuilder\PostType;
use ASMBS\ScheduleBuilder\Taxonomy\SessionTag;
use ASMBS\ScheduleBuilder\Taxonomy\SessionType;
use ASMBS\ScheduleBuilder\Taxonomy\Society;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Session extends AbstractModel {
    /** @var  string */
    protected $sessionID;

    /** @var  string */
    protected $title;

    /** @var  string */
    protected $content;

    /** @var  \DateTime */
    protected $startDate;

    /** @var  \DateTime */
    protected $endDate;

    /** @var  string */
    protected $venue;

    /** @var  string */
    protected $room;

    /** @var  string */
    protected $credits;

    /** @var  string[] */
    protected $creditTypes = [];

    /** @var  Helper\FacultyGroup[] */
    protected $facultyGroups = [];

    /** @var  null */
    protected $agendaItems;

    /** @var  \WP_Term[] */
    protected $societies = [];

    /** @var  \WP_Term */
    protected $sessionType;

    /** @var  \WP_Term[] */
    protected $tags = [];

    /** @var  string[] */
    protected $progress = [];

    /** @var  array */
    protected $progressChoices = [];

    /**
     * @return  array
     */
    public function getSupportedPostTypes() {
        return [
            PostType\Session::SLUG,
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return  string
     */
    public function getSessionID() {
        return $this->lazyLoad( 'sessionID', [ $this, 'loadField' ], 'session_id' );
    }

    /**
     * @param bool $filtered
     *
     * @return  string
     */
    public function getTitle( $filtered = true ) {
        $title = $this->lazyLoad( 'title', [ $this, 'loadPostProperty' ], 'post_title' );

        if ( $filtered ) {
            // Run standard the_content filter
            $title = apply_filters( 'the_title', $title );

            /**
             * Filter the session title.
             *
             * @param string $title
             *
             * @return  string
             */
            $title = apply_filters( 'sb/session_title', $title );
        }

        return $title;
    }

    /**
     * @param bool $filtered
     *
     * @return  string
     */
    public function getContent( $filtered = true ) {
        $content = $this->lazyLoad( 'content', [ $this, 'loadPostProperty' ], 'post_content' );

        if ( $filtered ) {
            // Run standard the_content filter
            $content = apply_filters( 'the_content', $content );

            /**
             * Filter the session content body _after_ `the_content` filters have been applied.
             *
             * @param string $content
             *
             * @return  string
             */
            $content = apply_filters( 'sb/session_content', $content );
        }

        return $content;
    }

    /**
     * @param string|bool $format
     *
     * @return  string|\DateTime
     */
    public function getDate( $format = 'l (n/j)' ) {
        $datetime = $this->lazyLoad( 'startDate', [ $this, 'loadFieldAsDateTime' ], 'date', 'start_time' );
        if ( $datetime instanceof \DateTime ) {
            return ( $format === false ) ? $datetime : $datetime->format( $format );
        }

        return 'TBA';
    }

    /**
     * @param string|bool $format
     *
     * @return  string|\DateTime
     */
    public function getStartTime( $format = 'g:ia' ) {
        return $this->getDate( $format );
    }

    /**
     * @param string|bool $format
     *
     * @return  string|\DateTime
     */
    public function getEndTime( $format = 'g:ia' ) {
        $datetime = $this->lazyLoad( 'endDate', [ $this, 'loadFieldAsDateTime' ], 'date', 'end_time' );
        if ( $datetime instanceof \DateTime ) {
            return ( $format === false ) ? $datetime : $datetime->format( $format );
        }

        return 'TBA';
    }

    /**
     * @return  string
     */
    public function getVenue() {
        return $this->lazyLoad( 'venue', [ $this, 'loadField' ], 'venue' );
    }

    /**
     * @return  string
     */
    public function getRoom() {
        return $this->lazyLoad( 'room', [ $this, 'loadField' ], 'room' );
    }

    /**
     * @return  string
     */
    public function getCredits() {
        $credits = $this->lazyLoad( 'credits', [ $this, 'loadField' ], 'credits_available' );
        $credits = $credits ? $credits : '0';

        return number_format( $credits, 2 );
    }

    /**
     * @return  string[]
     */
    public function getCreditTypes() {
        if ( $this->getCredits() > 0 ) {
            return (array) $this->lazyLoad( 'creditTypes', [ $this, 'loadField' ], 'credit_types' );
        }

        return [];
    }

    /**
     * @return  Helper\FacultyGroup[]
     */
    public function getFacultyGroups() {
        return $this->lazyLoad( 'facultyGroups', function ( Session $s ) {
            $groups = [];
            while ( have_rows( 'faculty_groups', $s->postID ) ) {
                the_row();
                $groups[] = new Helper\FacultyGroup( get_sub_field( 'label' ), get_sub_field( 'people' ) );
            }

            return $groups;
        }, $this );
    }

    /**
     * @return  Helper\AgendaItem[]
     */
    public function getAgendaItems() {
        return $this->lazyLoad( 'agendaItems', function ( Session $s ) {

            $items = [];

            // Create a map to translate ACF layout labels to agenda item types
            $layoutMap = [
                'item_simple'   => Helper\AgendaItemType::SIMPLE,
                'item_header'   => Helper\AgendaItemType::HEADER,
                'item_talk'     => Helper\AgendaItemType::TALK,
                'item_abstract' => Helper\AgendaItemType::_ABSTRACT,
                'item_break'    => Helper\AgendaItemType::_BREAK,
            ];

            while ( have_rows( 'agenda_items', $s->getPostID() ) ) {
                the_row();
                // Map item type
                $layout   = get_row_layout();
                $itemType = array_key_exists( $layout, $layoutMap )
                    ? $layoutMap[ $layout ]
                    : Helper\AgendaItemType::SIMPLE;

                $items[] = new Helper\AgendaItem( $itemType, $s );
            }

            return $items;
        }, $this );
    }

    /**
     * @param string|bool $field
     *
     * @return  \WP_Term[]
     */
    public function getSocieties( $field = false ) {
        return $this->lazyLoad( 'societies', [ $this, 'loadPostTerms' ], Society::SLUG, $field );
    }

    /**
     * @param string|bool $field
     *
     * @return  \WP_Term
     */
    public function getSessionType( $field = false ) {
        return $this->lazyLoad( 'sessionType', [ $this, 'loadSingleTerm' ], SessionType::SLUG, $field );
    }

    /**
     * @param string|bool $field
     *
     * @return  \WP_Term[]
     */
    public function getTags( $field = false ) {
        return $this->lazyLoad( 'tags', [ $this, 'loadPostTerms' ], SessionTag::SLUG, $field );
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Determine whether there are credits available for the session.
     *
     * @return  bool
     */
    public function hasCredits() {
        return ( $this->getCredits() > 0 && ! empty( $this->getCreditTypes() ) );
    }

    /**
     * Get the list of progess milestones that still need to be completed.
     *
     * @return  string[]
     */
    public function getProgressRemaining() {
        $choices = $this->getProgressChoices();
        $current = $this->getProgress();

        $diff = array_diff( array_keys( $choices ), $current );

        $remaining = [];
        foreach ( $diff as $key ) {
            $remaining[] = $choices[ $key ];
        }

        return $remaining;
    }

    /**
     * Determine whether the session is editorially complete.
     *
     * @return  bool
     */
    public function isComplete() {
        return ( count( $this->getProgressRemaining() ) === 0 );
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get the short name for a venue if there is one.
     *
     * @return  string
     */
    public function getVenueShortname() {
        $venues        = get_field( 'locations', 'sb_options' );
        $selectedVenue = $this->getVenue();

        foreach ( $venues as $venue ) {
            if ( isset( $venue['location_name'] ) && $venue['location_name'] === $selectedVenue ) {
                if ( isset( $venue['location_shortname'] ) ) {
                    return $venue['location_shortname'];
                }
            }
        }

        return $selectedVenue;
    }

    /**
     * @return  string[]
     */
    protected function getProgress() {
        return (array) $this->lazyLoad( 'progress', [ $this, 'loadField' ], 'progress' );
    }

    /**
     * @return  array
     */
    protected function getProgressChoices() {
        return $this->lazyLoad( 'progressChoices', function ( $ID ) {
            $field = get_field_object( 'staff_use--progress', $ID );
            if ( isset( $field['choices'] ) ) {
                return $field['choices'];
            }

            return [];
        }, $this->postID );
    }
}
