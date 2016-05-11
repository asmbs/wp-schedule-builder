<?php

namespace ASMBS\ScheduleBuilder\Model;

use ASMBS\ScheduleBuilder\PostType;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class Person extends AbstractModel
{
    /** @var  string */
    protected $prefix;

    /** @var  string */
    protected $first;

    /** @var  string */
    protected $mi;

    /** @var  string */
    protected $last;

    /** @var  string */
    protected $suffix;

    /** @var  string[] */
    protected $credentials;

    /** @var  string */
    protected $organization;

    /** @var  string */
    protected $title;

    /** @var  string */
    protected $city;

    /** @var  string */
    protected $state;

    /** @var  string */
    protected $country;

    /** @var  Helper\Image */
    protected $photo;

    /** @var  string */
    protected $bio;

    /**
     * @return  string[]
     */
    public function getSupportedPostTypes()
    {
        return [
            PostType\Speaker::SLUG,
            PostType\Author::SLUG,
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return  string
     */
    public function getPrefix()
    {
        return $this->lazyLoad('prefix', [$this, 'loadField'], 'prefix');
    }

    /**
     * @return  string
     */
    public function getFirst()
    {
        return $this->lazyLoad('first', [$this, 'loadField'], 'first');
    }

    /**
     * @return  string
     */
    public function getMiddle()
    {
        return $this->lazyLoad('mi', [$this, 'loadField'], 'mi');
    }

    /**
     * @return  string
     */
    public function getLast()
    {
        return $this->lazyLoad('last', [$this, 'loadField'], 'last');
    }

    /**
     * @return  string
     */
    public function getSuffix()
    {
        return $this->lazyLoad('suffix', [$this, 'loadField'], 'suffix');
    }

    /**
     * @return  string[]
     */
    public function getCredentials()
    {
        return $this->lazyLoad('credentials', function(Person $person) {
            $credentials = $person->loadField('credentials');
            if ($credentials) {
                return preg_split('/(?:,\s*|\s+)/i', $credentials);
            }

            return [];
        }, $this);
    }

    /**
     * @return  string
     */
    public function getOrganization()
    {
        return $this->lazyLoad('organization', [$this, 'loadField'], 'organization');
    }

    /**
     * @return  string
     */
    public function getTitle()
    {
        return $this->lazyLoad('title', [$this, 'loadField'], 'title');
    }

    /**
     * @return  string
     */
    public function getCity()
    {
        return $this->lazyLoad('city', [$this, 'loadField'], 'city');
    }

    /**
     * @return  string
     */
    public function getState()
    {
        return $this->lazyLoad('state', [$this, 'loadField'], 'state');
    }

    /**
     * @return  string
     */
    public function getCountry()
    {
        return $this->lazyLoad('country', [$this, 'loadField'], 'country');
    }

    /**
     * @return  Helper\Image
     */
    public function getPhoto()
    {
        return $this->lazyLoad('photo', [$this, 'loadImageField'], 'photo');
    }

    /**
     * @param   bool  $filtered
     * @return  string
     */
    public function getBio($filtered = true)
    {
        $bio = $this->lazyLoad('bio', [$this, 'loadField'], 'bio');

        if ($filtered) {
            // Run standard the_content filter
            $bio = apply_filters('the_content', $bio);

            /**
             * Filter the person's bio.
             *
             * @param   string  $bio
             * @return  string
             */
            $bio = apply_filters('sb/person_bio', $bio);
        }

        return $bio;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param   bool  $lastFirst
     * @return  string
     */
    public function getFormalName($lastFirst = false)
    {
        // Get all name components
        $prefix = $this->getPrefix();
        $first = $this->getFirst();
        $mi = $this->getMiddle();
        $last = $this->getLast();
        $suffix = $this->getSuffix();
        $credentials = $this->getCredentials();

        $first = $first ? $first : '(None)';
        $last = $last ? $last : '(None)';

        if ($lastFirst) {
            // Build last-first name format
            $format = '%4$s';
            if ($prefix) {
                $format .=', %1$s';
            }
            $format .= ', %2$s';
            if ($mi) {
                $format .= ' %3$s';
            }
            if ($suffix) {
                $format .=', %5$s';
            }
            if ($credentials) {
                $format .= ', %6$s';
            }
        } else {
            // Build first-first name format
            $format = '%2$s';
            if ($prefix) {
                $format = '%1$s '. $format;
            }
            if ($mi) {
                $format .= ' %3$s';
            }
            $format .= ' %4$s';
            if ($suffix) {
                $format .= ', %5$s';
            }
            if ($credentials) {
                $format .= ', %6$s';
            }
        }

        return sprintf($format, $prefix, $first, $mi, $last, $suffix, implode(' ', $credentials));
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function __toString()
    {
        return $this->getFormalName();
    }
}
