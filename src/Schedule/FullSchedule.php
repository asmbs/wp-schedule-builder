<?php

namespace ASMBS\ScheduleBuilder\Schedule;

use ASMBS\ScheduleBuilder\PostType\Session as SessionPostType;
use ASMBS\ScheduleBuilder\Model\Session as SessionModel;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class FullSchedule
{
    /** @var  ScheduleDay[] */
    protected $days = [];

    /** @var  string[] */
    protected $keywords = [];

    /** @var  array */
    protected $terms = [];

    public function __construct()
    {}

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Add a keyword filter.
     *
     * @param   string  $keyword
     * @return  $this
     */
    public function addKeyword($keyword)
    {
        $this->keywords[] = $keyword;

        return $this;
    }

    /**
     * Add a term filter.
     *
     * @param   string        $taxonomy
     * @param   string|array  $term
     * @return  $this
     */
    public function addTerm($taxonomy, $term)
    {
        foreach ((array) $term as $t) {
            $this->terms[$taxonomy][] = $t;
        }

        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Apply any set filters and build out the session listing.
     *
     * @return  $this
     */
    public function build()
    {
        // Build query arguments
        $queryArgs = [
            'posts_per_page' => -1,
            'post_type'      => SessionPostType::SLUG,
            'post_status'    => 'publish',
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'date',
                    'compare' => 'NOT IN',
                    'value'   => ['', 'tba'],
                ],
                [
                    'key'     => 'start_time',
                    'compare' => 'NOT IN',
                    'value'   => [null, ''],
                ],
                [
                    'key'     => 'end_time',
                    'compare' => 'NOT IN',
                    'value'   => [null, ''],
                ],
            ],
        ];

        // Apply keyword query filters
        $queryArgs['s'] = implode(' ', $this->keywords);

        // Apply taxonomy query filters
        foreach ($this->terms as $taxonomy => $terms) {
            $queryArgs['tax_query'][] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
            ];
        }

        // Run post query
        $posts = get_posts($queryArgs);

        // Sort and group results
        if (count($posts) > 0) {
            $days = [];
            foreach ($posts as $post)
            {
                $session = new SessionModel($post);
                $days[$session->getDate('Y/m/d')][] = $session;
            }

            ksort($days);

            foreach ($days as $date => &$sessions) {
                usort($sessions, [$this, 'sortSessions']);
                $this->days[] = new ScheduleDay($date, $sessions);
            }
        }


        return $this;
    }

    /**
     * Sort a list of sessions.
     *
     * @param   SessionModel  $a
     * @param   SessionModel  $b
     * @return  int
     */
    protected function sortSessions(SessionModel $a, SessionModel $b)
    {
        // Start time (asc)
        if (($start = $a->getStartTime('U') - $b->getStartTime('U')) !== 0) {
            return $start;
        }

        // End time (desc)
        if (($end = $b->getEndTime('U') - $a->getEndTime('U')) !== 0) {
            return $end;
        }

        // Society (asc)
        $societyMap = [
            1 => [],
            2 => ['ASMBS'],
            3 => ['TOS'],
            4 => ['ASMBS', 'TOS']
        ];
        sort($aSocieties = $a->getSocieties('name'));
        sort($bSocieties = $b->getSocieties('name'));
        $aValue = (int) array_search($aSocieties, $societyMap);
        $bValue = (int) array_search($bSocieties, $societyMap);
        if (($societyCmp = $aValue - $bValue) !== 0) {
            return $societyCmp;
        }

        // TODO: track

        // Title (asc)
        return strcasecmp($a->getTitle(), $b->getTitle());
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get the build day lists.
     *
     * @return ScheduleDay[]
     */
    public function getDays()
    {
        return $this->days;
    }
}
