<?php

namespace ASMBS\ScheduleBuilder\Model;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class AbstractModel implements ModelInterface
{
    /** @var  \WP_Post */
    protected $post;

    /** @var  \WP_Error */
    protected $errors;

    /** @var  int */
    protected $postID;

    /** @var  \WP_User */
    protected $author;

    /** @var  \DateTime */
    protected $dateModified;

    /**
     * {@inheritdoc}
     */
    public function __construct($post = null)
    {
        $this->errors = new \WP_Error();
        $post = get_post($post);

        if (!($post instanceof \WP_Post)) {
            $this->errors->add('INVALID', 'Expected a `WP_Post` or integer.', gettype($post));

            return;
        } elseif (!in_array($post->post_type, $this->getSupportedPostTypes())) {
            $this->errors->add('INVALID', 'Post type is not supported.', $post->post_type);

            return;
        }

        $this->post = $post;
        $this->postID = $post->ID;
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrors()
    {
        return ($this->errors instanceof \WP_Error && count($this->errors->get_error_messages()) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Merge errors from another WP_Error object into the one dedicated to this model object.
     *
     * @param  \WP_Error  $otherErrors
     */
    protected function importErrors(\WP_Error $otherErrors)
    {
        foreach ($otherErrors->get_error_codes() as $code) {
            $errors = $otherErrors->get_error_messages($code);
            $data = $otherErrors->get_error_data($code);
            for ($i = 0; $i < max(count($errors), count($data)); $i++) {
                if (array_key_exists($i, $errors)) {
                    $data = array_key_exists($i, $data) ? $data[$i] : null;
                    $this->errors->add($code, $errors[$i], $data);
                }
            }
        }
    }
    
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return  int
     */
    public function getPostID()
    {
        return $this->lazyLoad('postID', [$this, 'loadPostProperty'], 'ID');
    }

    /**
     * @return  \WP_User
     */
    public function getAuthor()
    {
        return $this->lazyLoad('author', 'get_user_by', 'ID', $this->post->post_author);
    }

    /**
     * @param   string|bool  $format
     * @return  string|\DateTime
     */
    public function getDateModified($format = 'm/d/y')
    {
        $datetime = $this->lazyLoad('dateModified', function(\WP_Post $post) {
            try {
                return new \DateTime($post->post_modified);
            } catch (\Exception $e) {
                return false;
            }
        }, $this->post);

        if ($datetime instanceof \DateTime) {
            return ($format === false) ? $datetime : $datetime->format($format);
        }

        return null;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Lazy-load a class property.
     *
     * @param   string    $prop
     * @param   callable  $callback
     * @param   mixed     $arg
     * @return  mixed
     */
    protected function lazyLoad($prop, callable $callback, $arg = null)
    {
        if ($this->{$prop} !== null) {
            return $this->{$prop};
        }

        $args = func_get_args();
        array_shift($args);
        array_shift($args);

        $this->{$prop} = call_user_func_array($callback, $args);

        return $this->{$prop};
    }

    /**
     * Get a property from the WP_Post instance.
     *
     * @param   string  $property
     * @return  mixed
     */
    protected function loadPostProperty($property)
    {
        if (isset($this->post->{$property})) {
            return $this->post->{$property};
        }

        return null;
    }

    /**
     * Load the terms from the given taxonomy associated with the underlying post.
     *
     * @param   string  $taxonomy
     * @return  \WP_Term[]
     */
    protected function loadPostTerms($taxonomy)
    {
        $terms = get_the_terms($this->post, $taxonomy);
        if ($terms instanceof \WP_Error) {
            $this->importErrors($terms);
        }

        if (is_array($terms)) {
            return $terms;
        }

        return [];
    }

    /**
     * Load a _single_ term associated with the post.
     *
     * @param   string  $taxonomy
     * @param   int     $index
     * @return  \WP_Term
     */
    protected function loadSingleTerm($taxonomy, $index = 0)
    {
        $terms = $this->loadPostTerms($taxonomy);

        if ($index > 0 && !array_key_exists($index, $terms)) {
            $index = 0;
        }

        return $terms[$index];
    }

    /**
     * Load a simple custom field.
     *
     * @param   string  $fieldName
     * @return  mixed
     */
    protected function loadField($fieldName)
    {
        return get_field($fieldName, $this->postID);
    }

    /**
     * Load a date field (and optionally a time field) as a DateTime object.
     *
     * @param   string  $dateField
     * @param   string  $timeField
     * @return  \DateTime|bool
     */
    protected function loadFieldAsDateTime($dateField, $timeField = null)
    {
        $str = get_field($dateField, $this->postID);
        if ($timeField) {
            $str .= ' '. get_field($timeField, $this->postID);
        }

        try {
            return new \DateTime($str);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load and format an image field.
     *
     * @param   string  $fieldName
     * @return  Helper\Image
     */
    protected function loadImageField($fieldName)
    {
        $field = $this->loadField($fieldName);
        
        return new Helper\Image($field);
    }
}
