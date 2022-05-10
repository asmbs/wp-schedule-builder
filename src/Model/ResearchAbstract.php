<?php

namespace ASMBS\ScheduleBuilder\Model;

use ASMBS\ScheduleBuilder\PostType;
use ASMBS\ScheduleBuilder\Taxonomy\ResearchAbstractKeyword;
use ASMBS\ScheduleBuilder\Taxonomy\ResearchAbstractType;
use ASMBS\ScheduleBuilder\Taxonomy\Society;
use ASMBS\ScheduleBuilder\Util\Timezones;
use WP_Term;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ResearchAbstract extends AbstractModel
{
    /** @var  string */
    protected $abstractID;

    /** @var  string */
    protected $title;

    /** @var  Person[] */
    protected $authors = [];

    /** @var  string */
    protected $introduction;

    /** @var  string */
    protected $methods;

    /** @var  string */
    protected $results;

    /** @var  string */
    protected $conclusions;

    /** @var  \DateTime */
    protected $embargoDate;

    /** @var  \WP_Term */
    protected $type;

    /** @var  \WP_Term[] */
    protected $societies = [];

    /** @var  \WP_Term[] */
    protected $keywords = [];

    /**
     * @return  string[]
     */
    public function getSupportedPostTypes()
    {
        return [
            PostType\ResearchAbstract::SLUG
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return  string
     */
    public function getAbstractID()
    {
        return $this->lazyLoad('abstractID', [$this, 'loadField'], 'abstract_id');
    }

    /**
     * @param bool $filtered
     *
     * @return  string
     */
    public function getTitle($filtered = false)
    {
        $title = $this->lazyLoad('title', [$this, 'loadField'], 'title');

        if ($filtered) {

            /**
             * Filter the abstract title.
             *
             * @param string $title
             *
             * @return  string
             *
             */
            $title = apply_filters('sb/abstract_title', $title);
        }

        return $title;
    }

    /**
     * @return  Person[]
     */
    public function getAuthors()
    {
        return $this->lazyLoad('authors', function ($ID) {
            // Get author field
            $authors = get_field('authors', $ID);

            // Convert post values to Person models
            if (is_array($authors)) {
                return array_map(function ($post) {
                    return new Person($post);
                }, $authors);
            } else {
                $authors = [];
            }

            return $authors;
        }, $this->postID);
    }

    /**
     * @return  string
     */
    public function getIntroduction()
    {
        return $this->lazyLoad('introduction', [$this, 'loadField'], 'introduction');
    }

    /**
     * @return  string
     */
    public function getMethods()
    {
        return $this->lazyLoad('methods', [$this, 'loadField'], 'methods');
    }

    /**
     * @return  string
     */
    public function getResults()
    {
        return $this->lazyLoad('results', [$this, 'loadField'], 'results');
    }

    /**
     * @return  string
     */
    public function getConclusions()
    {
        return $this->lazyLoad('conclusions', [$this, 'loadField'], 'conclusions');
    }

    /**
     * @param string|bool $format
     *
     * @return  \DateTime
     */
    public function getEmbargoDate($format = 'n/j')
    {
        $datetime = $this->lazyLoad('embargoDate', function ($ID) {
            // Get embargo date string
            $str = get_field('embargo_date', $ID);
            if ($str) {
                $str .= ' 07:00';
            }

            if (empty(trim($str))) {
                return false;
            }

            try {
                return new \DateTime($str, Timezones::getTimezone());
            } catch (\Exception $e) {
                return false;
            }
        }, $this->postID);

        if ($datetime instanceof \DateTime) {
            return ($format === false) ? $datetime : $datetime->format($format);
        }

        return null;
    }

    /**
     * @param string|bool $field
     *
     * @return  \WP_Term
     */
    public function getType($field = false)
    {
        return $this->lazyLoad('type', [$this, 'loadSingleTerm'], ResearchAbstractType::SLUG, $field);
    }

    /**
     * @param string|bool $field
     *
     * @return  \WP_Term[]
     */
    public function getSocieties($field = false)
    {
        return $this->lazyLoad('societies', [$this, 'loadPostTerms'], Society::SLUG, $field);
    }

    /**
     * @param string|bool $field
     *
     * @return  \WP_Term[]
     */
    public function getKeywords($field = false)
    {
        return $this->lazyLoad('keywords', [$this, 'loadPostTerms'], ResearchAbstractKeyword::SLUG, $field);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Determine whether an embargo is in effect for this abstract.
     *
     * @return  bool
     */
    public function isEmbargoed()
    {
        $embargo = $this->getEmbargoDate(false);

        if ($embargo) {
            return (new \DateTime('now', Timezones::getTimezone()) < $embargo);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {

        $embargo = $this->getEmbargoDate(false);

        if(null !== $embargo) {
            $data = array_merge(
                parent::jsonSerialize(),
                [
                    'abstract_id' => $this->getAbstractID(),
                    'name' => html_entity_decode($this->getTitle()),
                    'authors' => array_map(fn(Person $p) => $p->getPostID(), $this->getAuthors()),
                    'embargo_date' => $embargo->format('Y-m-d\TH:i:s.vP'),
                    'is_embargo' => $this->isEmbargoed()
                ]
            );
        }


        if (!$this->isEmbargoed()) {
            $data = array_merge($data, [
                'introduction' => html_entity_decode($this->getIntroduction()),
                'methods' => html_entity_decode($this->getMethods()),
                'results' => html_entity_decode($this->getResults()),
                'conclusions' => html_entity_decode($this->getConclusions()),
                'keywords' => array_map(fn(WP_Term $term): string => $term->name, $this->getKeywords())
            ]);
        }

        return array_filter($data);
    }
}
