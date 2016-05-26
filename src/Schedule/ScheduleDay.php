<?php

namespace ASMBS\ScheduleBuilder\Schedule;

use ASMBS\ScheduleBuilder\Model\Session;
use ASMBS\ScheduleBuilder\PostType\Session as SessionPostType;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ScheduleDay
{
    /** @var  string */
    protected $dateString;

    /** @var  Session[] */
    protected $sessions;

    /** @var  \DateTime */
    protected $start;

    /** @var  \DateTime */
    protected $startRounded;

    /** @var  \DateTime */
    protected $end;

    /** @var  \DateTime */
    protected $endRounded;

    public function __construct($dateString, array $sessions)
    {
        $this->dateString = $dateString;
        $this->sessions = $sessions;
    }

    /**
     * @return  Session[]
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * Count the number of sessions available on this day.
     * 
     * @return  int
     */
    public function countSessions()
    {
        return count($this->sessions);
    }

    /**
     * Load (lazily) the starting time of the earliest session of
     * the current day, even if that session has been filtered out
     * by search criteria.
     *
     * @param   string  $format
     * @param   bool    $round
     * @return  \DateTime|null|string
     */
    public function getStart($format = 'H:i:s', $round = false)
    {
        // Return if existing
        if ($this->start) {
            if ($round) {
                if (!$this->startRounded) {
                    $this->startRounded = $this->roundTime($this->start);
                }

                return ($format === false) ? $this->startRounded : $this->startRounded->format($format);
            }

            return ($format === false) ? $this->start : $this->start->format($format);
        }

        // Calculate otherwise
        global $wpdb;
        $string = $wpdb->get_var($wpdb->prepare(
            "SELECT MIN(STR_TO_DATE(CONCAT(d_.meta_value, ' ', s_.meta_value), '%%Y/%%m/%%d %%H:%%i')) "
            ."FROM {$wpdb->postmeta} d_ "
            ."JOIN {$wpdb->postmeta} s_ ON s_.post_id = d_.post_id AND s_.meta_key = 'start_time' AND s_.meta_value <> '' "
            ."JOIN {$wpdb->posts} p_ ON p_.ID = s_.post_id AND p_.post_type = '%2\$s' AND p_.status = 'publish' "
            ."WHERE d_.meta_value = '%1\$s'",
            $this->dateString,
            SessionPostType::SLUG
        ));

        try {
            $this->start = new \DateTime($string);

            if ($round) {
                $this->startRounded = $this->roundTime($this->start);

                return ($format === false) ? $this->startRounded : $this->startRounded->format($format);
            }

            return ($format === false) ? $this->start : $this->start->format($format);
        } catch (\Exception $e) {
            return $this->start = null;
        }
    }

    /**
     * Load (lazily) the ending time of the latest session of
     * the current day, even if that session has been filtered out
     * by search criteria.
     *
     * @param   string  $format
     * @param   bool    $round
     * @return  \DateTime|null|string
     */
    public function getEnd($format = 'H:i:s', $round = false)
    {
        // Return if existing
        if ($this->end) {
            if ($round) {
                if (!$this->endRounded) {
                    $this->endRounded = $this->roundTime($this->end, true);
                }

                return ($format === false) ? $this->endRounded : $this->endRounded->format($format);
            }

            return ($format === false) ? $this->end : $this->end->format($format);
        }

        // Calculate otherwise
        global $wpdb;
        $string = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(STR_TO_DATE(CONCAT(d_.meta_value, ' ', e_.meta_value), '%%Y/%%m/%%d %%H:%%i')) "
            ."FROM {$wpdb->postmeta} d_ "
            ."JOIN {$wpdb->postmeta} e_ ON e_.post_id = d_.post_id AND e_.meta_key = 'end_time' AND e_.meta_value <> '' "
            ."JOIN {$wpdb->posts} p_ ON p_.ID = e_.post_id AND p_.post_type = '%2\$s' AND p_.status = 'publish' "
            ."WHERE d_.meta_value = '%1\$s'",
            $this->dateString,
            SessionPostType::SLUG
        ));

        try {
            $this->end = new \DateTime($string);

            if ($round) {
                $this->endRounded = $this->roundTime($this->end, true);

                return ($format === false) ? $this->endRounded : $this->endRounded->format($format);
            }

            return ($format === false) ? $this->end : $this->end->format($format);
        } catch (\Exception $e) {
            return $this->end = null;
        }
    }

    /**
     * Round the time of a DateTime up or down to the nearest hour.
     *
     * @param   \DateTime  $orig
     * @param   bool       $up
     * @return  \DateTime
     */
    protected function roundTime(\DateTime $orig, $up = false)
    {
        $time = explode(':', $orig->format('H:i:s'));
        if ($up && $time[1] > 0) {
            $time[0]++;
        }

        $new = clone $orig;
        $new->setTime($time[0], 0);

        return $new;
    }
}
