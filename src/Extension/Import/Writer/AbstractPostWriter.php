<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\Extension\Import\ImporterInterface;
use Ddeboer\DataImport\Exception\WriterException;
use Ddeboer\DataImport\Writer\AbstractWriter;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class AbstractPostWriter extends AbstractWriter
{
    /** @var  ImporterInterface */
    protected $importer;

    /** @var  bool */
    protected $replace = false;

    /** @var  bool */
    protected $addNew = true;

    /** @var  array */
    protected $metaFields = [];

    /** @var  array */
    protected $terms = [];

    /**
     * @param  ImporterInterface  $importer
     * @param  bool               $replace
     * @param  bool               $addNew
     */
    public function __construct(ImporterInterface $importer, $replace, $addNew = true)
    {
        $this->importer = $importer;
        $this->replace = (bool) $replace;
        $this->addNew = (bool) $addNew;
    }

    /**
     * {@inheritdoc}
     */
    public function writeItem(array $item)
    {
        // Try to locate an existing post
        $post = $this->findExisting($item);
        if ($post && !$this->replace) {
            // If it exists and the overwrite flag is off, skip processing
            return $this;
        } elseif (!$post && $this->addNew) {
            // Otherwise, if the add-new flag is set, initialize a new post object
            $post = new \WP_Post((object) [
                'post_title'  => '(Untitled)',
                'post_type'   => $this->getPostType(),
                'post_status' => 'publish',
            ]);
        }

        if (!($post instanceof \WP_Post)) {
            // If we don't have a post object by now, skip the row
            return $this;
        }

        // Build the post and queue meta fields and terms
        $this->buildPost($post, $item)
            ->buildMetaFields($item)
            ->buildTerms($item);

        // Save the post
        $ID = $this->savePost($post);
        $post = get_post($ID);
        if (!$post) {
            $this->throwException('Post could not be saved.');
        }

        // Save meta fields and terms
        $this->saveMetaFields($post)
            ->saveTerms($post);

        return $this;
    }

    /**
     * Throw a writer exception.
     * 
     * @param   string      $message
     * @param   \Exception  $prev
     *
     * @throws  WriterException
     */
    public function throwException($message, \Exception $prev = null)
    {
        throw new WriterException($message, 0, $prev);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Determine whether the item already exists; if so, return it.
     *
     * @param   array  $item
     * @return  \WP_Post|bool
     */
    protected function findExisting(array $item)
    {
        return (is_array($posts = $this->queryPosts($item))) ? reset($posts) : false;
    }

    /**
     * Set post properties.
     *
     * @param   \WP_Post  $post
     * @param   array     $item
     * @return  $this
     */
    protected function buildPost(\WP_Post $post, array $item)
    {
        $post->post_author = get_current_user_id();
        $post->post_type = $this->getPostType();
        
        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Add a meta field to the storage queue.
     *
     * @param   string  $fieldName
     * @param   mixed   $value
     * @return  $this
     */
    final protected function addMeta($fieldName, $value)
    {
        $this->metaFields[$fieldName] = $value;

        return $this;
    }

    /**
     * Add a term to the storage queue.
     *
     * @param   string        $taxonomy
     * @param   string|array  $term
     * @return  $this
     */
    final protected function addTerm($taxonomy, $term)
    {
        foreach ((array) $term as $t) {
            $this->terms[$taxonomy][] = $t;
        }

        return $this;
    }

    /**
     * Insert or update the post.
     *
     * @param   \WP_Post  $post
     * @return  int
     */
    final protected function savePost(\WP_Post $post)
    {
        return wp_insert_post($post->to_array());
    }

    /**
     * Save all the queued meta fields.
     *
     * @param   \WP_Post  $post
     * @return  $this
     */
    final protected function saveMetaFields(\WP_Post $post)
    {
        foreach ($this->metaFields as $key => &$value) {
            update_field($key, $value, $post->ID);
        }

        // Trigger another save to make sure any save_post hooks that
        // depend on metadata get run.
        $this->savePost($post);

        return $this;
    }

    /**
     * Save all the queued taxonomy terms.
     *
     * @see  addTerm()
     *
     * @param   \WP_Post  $post
     * @return  $this
     */
    final protected function saveTerms(\WP_Post $post)
    {
        foreach ($this->terms as $taxonomy => $terms) {
            if (!is_object_in_taxonomy($post->post_type, $taxonomy)) {
                $this->throwException(sprintf('The "%s" taxonomy does not describe this post type.', $taxonomy));
            }

            $IDs = [];
            foreach ($terms as $term) {
                if ($termID = $this->getTermID($term, $taxonomy)) {
                    $IDs[] = $termID;
                }
            }

            if (count($IDs) > 0) {
                wp_set_object_terms($post->ID, $IDs, $taxonomy);
            }
        }

        return $this;
    }

    /**
     * Get the ID of a term if it already exists, or insert it and then get
     * its new ID.
     * 
     * @param   string  $term
     * @param   string  $taxonomy
     * @return  int
     */
    final protected function getTermID($term, $taxonomy)
    {
        $existing = term_exists($term, $taxonomy);
        if (is_array($existing)) {
            return (int) $existing['term_id'];
        }

        $new = wp_insert_term($term, $taxonomy);
        if (is_array($new)) {
            return (int) $new['term_id'];
        }

        $this->throwException(sprintf('The "%s" term could not be located or inserted.', $term));
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get the slug of the post type this writer interacts with.
     *
     * @return  string
     */
    abstract public function getPostType();

    /**
     * Query for posts matching the current item.
     *
     * @param   array  $item
     * @return  \WP_Post[]
     */
    abstract protected function queryPosts(array $item);

    /**
     * Build the meta fields that should be attached to this post once
     * it has been saved or updated.
     *
     * @see  addMeta()
     *
     * @param   array  $item
     * @return  $this
     */
    abstract protected function buildMetaFields(array $item);

    /**
     * Build the list of taxonomy terms that should be attached to this post
     * once it has been saved or updated.
     *
     * @param   array  $item
     * @return  $this
     */
    abstract protected function buildTerms(array $item);
}
