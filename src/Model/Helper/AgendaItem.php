<?php

namespace ASMBS\ScheduleBuilder\Model\Helper;

use ASMBS\ScheduleBuilder\Model\AbstractModel;
use ASMBS\ScheduleBuilder\Model\Person;
use ASMBS\ScheduleBuilder\Model\ResearchAbstract;
use ASMBS\ScheduleBuilder\Model\Session;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class AgendaItem implements \JsonSerializable {

    /** @var  string */
    protected $type;

    /** @var  Session */
    protected $session;

    /** @var  \DateTime */
    protected $start;

    /** @var  \DateTime */
    protected $end;

    /** @var  string */
    protected $title;

    /** @var  string */
    protected $facultyLabel;

    /** @var  Person[] */
    protected $faculty = [];

    /** @var  Person */
    protected $presenter;

    /** @var  ResearchAbstract */
    protected $abstract;

    /** @var Person[] */
    protected $speakers = [];

    /** @var  Person[] */
    protected $discussants = [];

    /** @var  string */
    protected $breakType;

    /**
     * Constructor; sets item properties.
     *
     * NOTE: Objects of this class MUST be instantiated from within an ACF
     *
     * @param string $type
     * @param Session $session
     */
    public function __construct( $type, Session $session ) {
        $this->type    = $type;
        $this->session = $session;

        $this->setGlobalFields()
             ->setSimpleFields()
             ->setHeaderFields()
             ->setTalkFields()
             ->setAbstractFields()
             ->setBreakFields();
    }

    protected function setGlobalFields() {
        // Get date string from session
        $sessionDate = $this->session->getDate( 'm/d/Y' );
        $startTime   = get_sub_field( 'start_time' );
        $endTime     = get_sub_field( 'end_time' );

        if ( $sessionDate != 'TBA' && ! empty( $startTime ) ) {
            try {
                $this->start = new \DateTime( $sessionDate . ' ' . $startTime,  \ASMBS\ScheduleBuilder\PostType\Session::getTimezone() );
                $this->end   = new \DateTime( $sessionDate . ' ' . $endTime,  \ASMBS\ScheduleBuilder\PostType\Session::getTimezone());
            } catch ( \Exception $e ) {
            }
        }

        return $this;
    }

    protected function setSimpleFields() {
        if ( $this->type === AgendaItemType::SIMPLE ) {
            $this->title = get_sub_field( 'title' );
        }

        return $this;
    }

    protected function setHeaderFields() {
        if ( $this->type === AgendaItemType::HEADER ) {
            $this->title        = get_sub_field( 'section_title' );
            $this->facultyLabel = get_sub_field( 'faculty_label' );

            $faculty = get_sub_field( 'people' );
            if ( is_array( $faculty ) ) {
                foreach ( $faculty as $post ) {
                    $this->faculty[] = new Person( $post );
                }
            }
        }

        return $this;
    }

    protected function setTalkFields() {
        if ( $this->type === AgendaItemType::TALK ) {
            $this->title = get_sub_field( 'talk_title' );
            if ( is_array( $speakers = get_sub_field( 'speaker' ) ) ) {
                foreach ( $speakers as $post ) {
                    $this->speakers[] = new Person( $post );
                }
            }
            $speaker = get_sub_field( 'speaker' );
            if ( ! empty( $speaker ) ) {
                $this->presenter = new Person( $speaker );
            }
        }

        return $this;
    }

    protected function setAbstractFields() {
        if ( $this->type === AgendaItemType::_ABSTRACT ) {
            if ( ! empty( $abstract = get_sub_field( 'abstract' ) ) ) {
                $this->abstract = new ResearchAbstract( $abstract );
            }
            if ( ! empty( $presenter = get_sub_field( 'presenter' ) ) ) {
                $this->presenter = new Person( $presenter );
            }
            if ( is_array( $discussants = get_sub_field( 'discussants' ) ) ) {
                foreach ( $discussants as $post ) {
                    $this->discussants[] = new Person( $post );
                }
            }
        }

        return $this;
    }

    protected function setBreakFields() {
        if ( $this->type === AgendaItemType::_BREAK ) {
            $this->breakType = ucfirst( get_sub_field( 'break_type' ) );
        }

        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return  string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return  Session
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * @param string|bool $format
     *
     * @return \DateTime|string
     */
    public function getStart( $format = 'g:ia' ) {
        if ( ! $this->start ) {
            return 'TBA';
        }

        return ( $format === false ) ? $this->start : $this->start->format( $format );
    }

    /**
     * @param string|bool $format
     *
     * @return \DateTime|string
     */
    public function getEnd( $format = 'g:ia' ) {
        if ( ! $this->end ) {
            return 'TBA';
        }

        return ( $format === false ) ? $this->end : $this->end->format( $format );
    }

    /**
     * @return  string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return  string
     */
    public function getFacultyLabel() {
        return $this->facultyLabel;
    }

    /**
     * @return  Person[]
     */
    public function getFaculty() {
        return $this->faculty;
    }

    /**
     * @return  ResearchAbstract
     */
    public function getAbstract() {
        return $this->abstract;
    }

    /**
     * @return  Person
     */
    public function getPresenter() {
        return $this->presenter;
    }

    /**
     * Alias of {@see getPresenter()}.
     *
     * @return  Person
     */
    public function getSpeaker() {
        return $this->getPresenter();
    }

    /**
     * @return Person[]
     */
    public function getSpeakers() {
        return $this->speakers;
    }

    /**
     * @return  Person[]
     */
    public function getDiscussants() {
        return $this->discussants;
    }

    /**
     * @return  string
     */
    public function getBreakType() {
        return $this->breakType;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Determine whether the item is the given type.
     *
     * @param string|array $type
     *
     * @return  bool
     */
    public function isType( $type ) {
        foreach ( (array) $type as $t ) {
            if ( $this->type === $t ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the item has faculty.
     * Valid types: [HEADER]
     *
     * @return  bool
     */
    public function hasFaculty() {
        if ( ! $this->isType( AgendaItemType::HEADER ) ) {
            return false;
        }

        return ( ! empty( $this->getFacultyLabel() ) && count( $this->getFaculty() ) > 0 );
    }

    /**
     * Determine whether the item has a presenter.
     * Valid types: [TALK, ABSTRACT]
     *
     * @return  bool
     */
    public function hasPresenter() {
        if ( ! $this->isType( [ AgendaItemType::TALK, AgendaItemType::_ABSTRACT ] ) ) {
            return false;
        }

        return ( $this->getPresenter() instanceof Person );
    }

    /**
     * Alias of {@see hasPresenter()}.
     *
     * @return  bool
     */
    public function hasSpeaker() {
        return $this->hasPresenter();
    }

    public function hasSpeakers() {
        if ( ! $this->isType( AgendaItemType::TALK ) ) {
            return false;
        }

        return ( count( $this->getSpeakers() ) > 0 );
    }

    /**
     * Determine whether the item has an abstract.
     * Valid types: [ABSTRACT]
     *
     * @return  bool
     */
    public function hasAbstract() {
        if ( ! $this->isType( AgendaItemType::_ABSTRACT ) ) {
            return false;
        }

        return ( $this->getAbstract() instanceof ResearchAbstract );
    }

    /**
     * @return  bool
     */
    public function hasAuthors() {
        if ( ! $this->isType( AgendaItemType::_ABSTRACT ) ) {
            return false;
        }

        return ( $this->hasAbstract() && count( $this->getAbstract()->getAuthors() ) > 0 );
    }


    /**
     * Determine whether the item has discussants assigned.
     * Valid types: [ABSTRACT]
     *
     * @return  bool
     */
    public function hasDiscussants() {
        if ( ! $this->isType( AgendaItemType::_ABSTRACT ) ) {
            return false;
        }

        return ( $this->hasAbstract() && count( $this->getDiscussants() ) > 0 );
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'type' => strtolower($this->getType()),
            'name' => $this->getTitle(),
            'start_time' => $this->getStart('Y-m-d\TH:i:s.vP'),
            'end_time' => $this->getEnd('Y-m-d\TH:i:s.vP'),
            //'presenter' => $this->getPresenter(),
            //'speakers' => $this->getSpeakers(),
            //'faculty' => $this->getFaculty(),
            //'faculty_label' => $this->getFacultyLabel(),
            //'discussants' => $this->getDiscussants(),
            //'abstract' => $this->getAbstract(),
            //'breakType' => $this->getBreakType()
        ]);
    }
}
