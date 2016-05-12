<?php

namespace ASMBS\ScheduleBuilder\Model;

use ASMBS\ScheduleBuilder\PostType;
use ASMBS\ScheduleBuilder\Taxonomy\ResearchAbstractKeyword;
use ASMBS\ScheduleBuilder\Taxonomy\ResearchAbstractType;
use ASMBS\ScheduleBuilder\Taxonomy\Society;

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
     * @return  string
     */
    public function getTitle()
    {
        return $this->lazyLoad('title', [$this, 'loadField'], 'title');
    }

    /**
     * @return  Person[]
     */
    public function getAuthors()
    {
        return $this->lazyLoad('authors', function($ID) {
            // Get author field
            $authors = get_field('authors', $ID);

            // Convert post values to Person models
            if (is_array($authors)) {
                return array_map(function($post) {
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
     * @param   string|bool  $format
     * @return  \DateTime
     */
    public function getEmbargoDate($format = 'n/j')
    {
        $datetime = $this->lazyLoad('embargoDate', function($ID) {
            // Get embargo date string
            $str = get_field('embargo_date', $ID);
            if ($str) {
                $str .= ' 07:00';
            }

            if (empty(trim($str))) {
                return false;
            }

            try {
                return new \DateTime($str);
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
     * @param   string|bool  $field
     * @return  \WP_Term
     */
    public function getType($field = false)
    {
        return $this->lazyLoad('type', [$this, 'loadSingleTerm'], ResearchAbstractType::SLUG, $field);
    }

    /**
     * @param   string|bool  $field
     * @return  \WP_Term[]
     */
    public function getSocieties($field = false)
    {
        return $this->lazyLoad('societies', [$this, 'loadPostTerms'], Society::SLUG, $field);
    }

    /**
     * @param   string|bool  $field
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
            return ((new \DateTime()) < $embargo);
        }

        return false;
    }
}
