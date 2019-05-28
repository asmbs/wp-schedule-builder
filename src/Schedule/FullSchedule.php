<?php

namespace ASMBS\ScheduleBuilder\Schedule;

use ASMBS\ScheduleBuilder\Model\Session as SessionModel;
use ASMBS\ScheduleBuilder\PostType\Session as SessionPostType;
use ASMBS\ScheduleBuilder\Taxonomy\SessionTag;
use ASMBS\ScheduleBuilder\Taxonomy\SessionType;
use ASMBS\ScheduleBuilder\Taxonomy\Society;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class FullSchedule {
    /** @var  ScheduleDay[] */
    protected $days = [];

    /** @var  string[] */
    protected $keywords = [];

    /** @var  string[] */
    protected $sessionTypes = [];

    /** @var  string[] */
    protected $sessionTags = [];

    /** @var  string[] */
    protected $societies = [];

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Add a keyword filter.
     *
     * @param string $keyword
     *
     * @return  $this
     */
    public function addKeyword( $keyword ) {
        $this->keywords[] = $keyword;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return  $this
     */
    public function addSessionType( $type ) {
        $this->sessionTypes[] = $type;

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return  $this
     */
    public function addSessionTag( $tag ) {
        $this->sessionTags[] = $tag;

        return $this;
    }

    /**
     * @param string $society
     *
     * @return  $this
     */
    public function addSociety( $society ) {
        $this->societies[] = $society;

        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Apply any set filters and build out the session listing.
     *
     * @return  $this
     */
    public function build() {
        // Build query arguments
        $queryArgs = [
            'posts_per_page' => - 1,
            'post_type'      => SessionPostType::SLUG,
            'post_status'    => 'publish',
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'date',
                    'compare' => 'NOT IN',
                    'value'   => [ '', 'tba' ],
                ],
                [
                    'key'     => 'start_time',
                    'compare' => 'NOT IN',
                    'value'   => [ null, '' ],
                ],
                [
                    'key'     => 'end_time',
                    'compare' => 'NOT IN',
                    'value'   => [ null, '' ],
                ],
            ],
        ];

        // Apply keyword query filters
        $queryArgs['s'] = implode( ' ', $this->keywords );

        // Apply session type filters (ORed)
        if ( count( $this->getSessionTypes() ) > 0 ) {
            $queryArgs['tax_query'][] = [
                'taxonomy' => SessionType::SLUG,
                'field'    => 'slug',
                'terms'    => $this->getSessionTypes(),
            ];
        }

        // Apply session tag filters (ORed)
        if ( count( $this->getSessionTags() ) > 0 ) {
            $queryArgs['tax_query'][] = [
                'taxonomy' => SessionTag::SLUG,
                'field'    => 'slug',
                'terms'    => $this->getSessionTags(),
            ];
        }

        // Apply society filters (ANDed)
        if ( count( $this->getSocieties() ) > 0 ) {
            $taxQuery = [
                'relation' => 'AND',
            ];
            foreach ( $this->getSocieties() as $society ) {
                $taxQuery[] = [
                    'taxonomy' => Society::SLUG,
                    'field'    => 'slug',
                    'terms'    => $society,
                ];
            }

            $queryArgs['tax_query'][] = $taxQuery;
        }

        if ( isset( $queryArgs['tax_query'] ) ) {
            $queryArgs['tax_query']['relation'] = 'AND';
        }

        // Run post query
        $posts = get_posts( $queryArgs );

        // Sort and group results
        if ( count( $posts ) > 0 ) {
            $days = [];
            foreach ( $posts as $post ) {
                $session                                = new SessionModel( $post );
                $days[ $session->getDate( 'Y/m/d' ) ][] = $session;
            }

            ksort( $days );

            foreach ( $days as $date => &$sessions ) {
                usort( $sessions, [ $this, 'sortSessions' ] );
                $this->days[] = new ScheduleDay( $date, $sessions );
            }
        }


        return $this;
    }

    /**
     * Sort a list of sessions.
     *
     * @param SessionModel $a
     * @param SessionModel $b
     *
     * @return  int
     */
    protected function sortSessions( SessionModel $a, SessionModel $b ) {
        // Start time (asc)
        if ( ( $start = $a->getStartTime( 'U' ) - $b->getStartTime( 'U' ) ) !== 0 ) {
            return $start;
        }

        // End time (desc)
        if ( ( $end = $b->getEndTime( 'U' ) - $a->getEndTime( 'U' ) ) !== 0 ) {
            return $end;
        }

        // Society (asc)
        $societyMap = [
            1 => [],
            2 => [ 'ASMBS' ],
            3 => [ 'TOS' ],
            4 => [ 'ASMBS', 'TOS' ]
        ];

        $aSocieties = $a->getSocieties( 'name' );
        $bSocieties = $b->getSocieties( 'name' );
        sort( $aSocieties );
        sort( $bSocieties );
        $aValue = (int) array_search( $aSocieties, $societyMap );
        $bValue = (int) array_search( $bSocieties, $societyMap );

        if ( ( $societyCmp = $aValue - $bValue ) !== 0 ) {
            return $societyCmp;
        }

        // Title (asc)
        return strcasecmp( $a->getTitle(), $b->getTitle() );
    }

    /**
     * Reduction callback to add the session count for the given day to the previous count.
     *
     * @param int $prevCount
     * @param ScheduleDay $day
     *
     * @return  mixed
     */
    protected function appendSessionCount( $prevCount, ScheduleDay $day ) {
        return $prevCount + $day->countSessions();
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get the build day lists.
     *
     * @return ScheduleDay[]
     */
    public function getDays() {
        return $this->days;
    }

    /**
     * Count the total number of sessions available for _all_ days.
     *
     * @return  int
     */
    public function countSessions() {
        return array_reduce( $this->days, [ $this, 'appendSessionCount' ], 0 );
    }

    /**
     * @return  array
     */
    public function getKeywords() {
        return array_values( array_unique( $this->keywords ) );
    }

    /**
     * @return  array
     */
    public function getSessionTypes() {
        return array_values( array_unique( $this->sessionTypes ) );
    }

    /**
     * @return  array
     */
    public function getSessionTags() {
        return array_values( array_unique( $this->sessionTags ) );
    }

    /**
     * @return  array
     */
    public function getSocieties() {
        return array_values( array_unique( $this->societies ) );
    }

    /**
     * Determine whether any filters have been applied to this schedule listing.
     *
     * @return  bool
     */
    public function hasFilters() {
        return array_sum( [
                count( $this->keywords ),
                count( $this->sessionTypes ),
                count( $this->sessionTags ),
                count( $this->societies ),
            ] ) > 0;
    }
}
