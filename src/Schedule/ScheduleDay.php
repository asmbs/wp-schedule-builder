<?php

namespace ASMBS\ScheduleBuilder\Schedule;

use ASMBS\ScheduleBuilder\Model\Session;


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
    protected $end;

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
     * Load (lazily) the starting time of the earliest session of
     * the current day, even if that session has been filtered out
     * by search criteria.
     *
     * @param   string  $format
     * @return  \DateTime|null|string
     */
    public function getStart($format = 'n/j/y')
    {
        // Return if existing
        if ($this->start) {
            return ($format === false) ? $this->start : $this->start->format($format);
        }

        // Calculate otherwise
        global $wpdb;
        $string = $wpdb->get_var($wpdb->prepare(
            "SELECT MIN(STR_TO_DATE(CONCAT(d_.meta_value, ' ', s_.meta_value), '%%Y/%%m/%%d %%H:%%i')) "
            ."FROM {$wpdb->postmeta} d_ "
            ."JOIN {$wpdb->postmeta} s_ ON s_.post_id = d_.post_id AND s_.meta_key = 'start_time' "
            ."WHERE d_.meta_value = '%s'",
            $this->dateString
        ));

        try {
            $this->start = new \DateTime($string);

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
     * @return  \DateTime|null|string
     */
    public function getEnd($format = 'n/j/y')
    {
        // Return if existing
        if ($this->end) {
            return ($format === false) ? $this->end : $this->end->format($format);
        }

        // Calculate otherwise
        global $wpdb;
        $string = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(STR_TO_DATE(CONCAT(d_.meta_value, ' ', e_.meta_value), '%%Y/%%m/%%d %%H:%%i')) "
            ."FROM {$wpdb->postmeta} d_ "
            ."JOIN {$wpdb->postmeta} e_ ON e_.post_id = d_.post_id AND e_.meta_key = 'end_time' "
            ."WHERE d_.meta_value = '%s'",
            $this->dateString
        ));

        try {
            $this->end = new \DateTime($string);

            return ($format === false) ? $this->end : $this->end->format($format);
        } catch (\Exception $e) {
            return $this->end = null;
        }
    }
}
