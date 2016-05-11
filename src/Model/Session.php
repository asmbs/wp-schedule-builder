<?php

namespace ASMBS\ScheduleBuilder\Model;

use ASMBS\ScheduleBuilder\PostType;
use ASMBS\ScheduleBuilder\Taxonomy\SessionTag;
use ASMBS\ScheduleBuilder\Taxonomy\SessionType;
use ASMBS\ScheduleBuilder\Taxonomy\Society;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Session extends AbstractModel
{
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
    protected $creditTypes;

    /** @var  null */
    protected $facultyGroups;
    
    /** @var  null */
    protected $agendaItems;

    /** @var  \WP_Term[] */
    protected $societies;

    /** @var  \WP_Term */
    protected $sessionType;

    /** @var  \WP_Term[] */
    protected $tags;

    /** @var  string[] */
    protected $progress;

    /**
     * @return  array
     */
    public function getSupportedPostTypes()
    {
        return [
            PostType\Session::SLUG,
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return  string
     */
    public function getSessionID()
    {
        return $this->lazyLoad('sessionID', [$this, 'loadField'], 'session_id');
    }

    /**
     * @param   bool  $filtered
     * @return  string
     */
    public function getTitle($filtered = true)
    {
        $title = $this->lazyLoad('title', [$this, 'loadPostProperty'], 'post_title');

        if ($filtered) {
            // Run standard the_content filter
            $title = apply_filters('the_title', $title);

            /**
             * Filter the session title.
             *
             * @param   string  $title
             * @return  string
             */
            $title = apply_filters('sb/session_title', $title);
        }

        return $title;
    }

    /**
     * @param   bool  $filtered
     * @return  string
     */
    public function getContent($filtered = true)
    {
        $content = $this->lazyLoad('content', [$this, 'loadPostProperty'], 'post_content');

        if ($filtered) {
            // Run standard the_content filter
            $content = apply_filters('the_content', $content);

            /**
             * Filter the session content body _after_ `the_content` filters have been applied.
             *
             * @param   string  $content
             * @return  string
             */
            $content = apply_filters('sb/session_content', $content);
        }

        return $content;
    }

    /**
     * @param   string|bool  $format
     * @return  string|\DateTime
     */
    public function getDate($format = 'l (n/j)')
    {
        $datetime = $this->lazyLoad('startDate', [$this, 'loadFieldAsDateTime'], 'date', 'start_time');
        if ($datetime instanceof \DateTime) {
            return ($format === false) ? $datetime : $datetime->format($format);
        }

        return null;
    }

    /**
     * @param   string|bool  $format
     * @return  string|\DateTime
     */
    public function getStartTime($format = 'g:ia')
    {
        return $this->getDate($format);
    }

    /**
     * @param   string|bool  $format
     * @return  string|\DateTime
     */
    public function getEndTime($format = 'g:ia')
    {
        $datetime = $this->lazyLoad('endDate', [$this, 'loadFieldAsDateTime'], 'date', 'end_time');
        if ($datetime instanceof \DateTime) {
            return ($format === false) ? $datetime : $datetime->format($format);
        }

        return null;
    }

    /**
     * @return  string
     */
    public function getVenue()
    {
        return $this->lazyLoad('venue', [$this, 'loadField'], 'venue');
    }

    /**
     * @return  string
     */
    public function getRoom()
    {
        return $this->lazyLoad('room', [$this, 'loadField'], 'room');
    }

    /**
     * @return  string
     */
    public function getCredits()
    {
        $credits = $this->lazyLoad('credits', [$this, 'loadField'], 'credits_available');
        $credits = $credits ? $credits : '0';

        return number_format($credits, 2);
    }

    /**
     * @return  string[]
     */
    public function getCreditTypes()
    {
        if ($this->getCredits() > 0) {
            return (array) $this->lazyLoad('creditTypes', [$this, 'loadField'], 'credit_types');
        }

        return [];
    }

    /**
     * TODO
     */
    public function getFacultyGroups()
    {}

    /**
     * TODO
     */
    public function getAgendaItems()
    {}

    /**
     * @return  \WP_Term[]
     */
    public function getSocieties()
    {
        return $this->lazyLoad('societies', [$this, 'loadPostTerms'], Society::SLUG);
    }

    /**
     * @return  \WP_Term
     */
    public function getSessionType()
    {
        return $this->lazyLoad('sessionType', [$this, 'loadSingleTerm'], SessionType::SLUG);
    }

    /**
     * @return  \WP_Term[]
     */
    public function getTags()
    {
        return $this->lazyLoad('tags', [$this, 'loadPostTerms'], SessionTag::SLUG);
    }

    /**
     * @return  string[]
     */
    public function getProgress()
    {
        return (array) $this->lazyLoad('progress', [$this, 'loadField'], 'progress');
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Determine whether there are credits available for the session.
     *
     * @return  bool
     */
    public function hasCredits()
    {
        return ($this->getCredits() > 0 && !empty($this->getCreditTypes()));
    }

    /**
     * Determine whether the session is editorially complete.
     *
     * @return  bool
     */
    public function isComplete()
    {
        return (count($this->getProgress()) === 5);
    }
}
