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
                if (in_array('TBA', [
                    $session->getStartTime(false),
                    $session->getEndTime(false),
                ], true)) {
                    continue;
                }

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

        $aSocieties = $a->getSocieties('name');
        $bSocieties = $b->getSocieties('name');
        sort($aSocieties);
        sort($bSocieties);
        $aValue = (int) array_search($aSocieties, $societyMap);
        $bValue = (int) array_search($bSocieties, $societyMap);

        if (($societyCmp = $aValue - $bValue) !== 0) {
            return $societyCmp;
        }

        // TODO: track

        // Title (asc)
        return strcasecmp($a->getTitle(), $b->getTitle());
    }

    /**
     * Reduction callback to add the session count for the given day to the previous count.
     *
     * @param   int          $prevCount
     * @param   ScheduleDay  $day
     * @return  mixed
     */
    protected function appendSessionCount($prevCount, ScheduleDay $day)
    {
        return $prevCount + $day->countSessions();
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

    /**
     * Count the total number of sessions available for _all_ days.
     *
     * @return  int
     */
    public function countSessions()
    {
        return array_reduce($this->days, [$this, 'appendSessionCount'], 0);
    }
}
