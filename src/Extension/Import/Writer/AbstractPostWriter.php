<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\Extension\Import\ImporterInterface;
use Port\Writer\AbstractStreamWriter;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class AbstractPostWriter extends AbstractStreamWriter
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

        // Clear meta field and term queues
        $this->metaFields = [];
        $this->terms = [];

        // Build the post and queue meta fields and terms
        $this->buildPost($post, $item)
            ->buildMetaFields($post, $item)
            ->buildTerms($post, $item);

        // Save the post
        $ID = $this->savePost($post);
        $post = get_post($ID);
        if (!$post) {
            $this->throwException('Post could not be saved.');
        }

        // Save meta fields and terms
        $this->saveMetaFields($post)
            ->saveTerms($post);

        // Flush the cache
        wp_cache_flush();
        
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
        if (!empty($value)) {
            $this->metaFields[$fieldName] = $value;
        }

        return $this;
    }

    /**
     * @param $fieldName
     * @param $url
     * @param $name
     *
     * @return $this
     * @internal param $filename
     *
     */
    final protected function addAttachment($fieldName, $url, $name)
    {
        if (!empty($url)) {

            // Check if file exists
            $headers = @get_headers($url);
            if (!$headers || strpos($headers[0], '404'))
                $headers = false;

            // Check for file type
            $pathinfo = pathinfo($url);
            $validTypes = ['jpg', 'jpeg','png',];

            // Continue if valid file type and url exists
            if (in_array($pathinfo['extension'], $validTypes) && $headers) {

                // set image file name and add extension
                $filename = $name . '.' . $pathinfo['extension'];

                // set upload path
                $dir = wp_upload_dir();
                $file = $dir['path'] . '/' . $filename;

                // upload image file
                $contents = file_get_contents($url);
                $save = fopen($file, 'w');
                fwrite($save, $contents);
                fclose($save);

                // prepare metadata
                $fileType = wp_check_filetype(basename($filename), null);
                $attachment = [
                    'post_mime_type' => $fileType['type'],
                    'post_title' => $name,
                    'post_content' => '',
                    'post_status' => 'inherit',
                ];

                // add image to media library as attachment
                if (!function_exists('wp_insert_attachment')) {
                    require_once (ABSPATH . 'wp-admin' . '/includes/image.php');
                }

                $id = wp_insert_attachment($attachment, $file);

                // save image metadata
                $newAttachment = get_post($id);
                $relativePath = get_attached_file($newAttachment->ID);
                $metadata = wp_generate_attachment_metadata($id, $relativePath);
                wp_update_attachment_metadata($id, $metadata);

                // return image ID to storage queue
                $this->metaFields[$fieldName] = $id;

            }
        }

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
        $terms = array_filter((array) $term);
        if ($terms) {
            foreach ($terms as $t) {
                $this->terms[$taxonomy][] = $t;
            }
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
                // Set terms
                $t = wp_set_post_terms($post->ID, $IDs, $taxonomy);

                // If a corresponding ACF taxonomy field exists, populate it
                $this->syncTaxonomyMeta($post, $t, $taxonomy);
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

        return $this->throwException(sprintf('The "%s" term could not be located or inserted.', $term));
    }

    /**
     * Synchronize ACF field metadata for taxonomy fields.
     *
     * ACF's taxonomy fields set term relationships in the same way the core taxonomy API works, but it also
     * saves those term relationships in post meta fields -- the latter is what ACF uses to set the correct values
     * in the post editor. Calling this method during {@see saveTerms()} will ensure that if ACF is managing those
     * taxonomies, the corresponding post fields are updated.
     *
     * @param   \WP_Post  $post
     * @param   array     $terms
     * @param   string    $taxonomy
     * @return  $this
     */
    protected function syncTaxonomyMeta(\WP_Post $post, array $terms, $taxonomy)
    {
        // Build a list of possible field keys
        $selectors = [
            sprintf('taxonomy--%s', str_replace('-', '_', $taxonomy)),
            sprintf('taxonomy--%s', $taxonomy),
        ];

        foreach ($selectors as &$selector) {
            // Try to find a field definition for the selector
            $field = get_field_object($selector, $post->ID);

            if (is_array($field) && $field['type'] === 'taxonomy') {
                // If the field is defined and is definitely a taxonomy field, set the field
                // for this post
                update_field($field['key'], $terms, $post->ID);

                break;
            }
        }

        return $this;
    }

    /**
     * Find posts of the given post type that match the given meta queries.
     *
     * @see  \WP_Meta_Query
     *
     * @param   string  $postType
     * @param   array   $metaQuery
     * @param   bool    $multiple
     * @return  \WP_Post|\WP_Post[]
     */
    protected function findPostsWithMeta($postType, array $metaQuery, $multiple = false)
    {
        $posts = get_posts([
            'post_type'   => $postType,
            'post_status' => 'any',
            'meta_query'  => $metaQuery,
            'posts_per_page' => -1,
        ]);

        if (count($posts) > 0) {
            return $multiple ? $posts : reset($posts);
        }

        return $multiple ? [] : null;
    }

    /**
     * Array map callback; returns the ID of a post object.
     *
     * @param   \WP_Post  $post
     * @return  int
     */
    protected function getPostID(\WP_Post $post)
    {
        return $post->ID;
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
     * @param   \WP_Post  $post
     * @param   array     $item
     * @return  $this
     */
    abstract protected function buildMetaFields(\WP_Post $post, array $item);

    /**
     * Build the list of taxonomy terms that should be attached to this post
     * once it has been saved or updated.
     *
     * @param   \WP_Post  $post
     * @param   array     $item
     * @return  $this
     */
    abstract protected function buildTerms(\WP_Post $post, array $item);
}
