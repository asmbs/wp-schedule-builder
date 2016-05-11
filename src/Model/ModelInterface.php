<?php

namespace ASMBS\ScheduleBuilder\Model;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
interface ModelInterface
{
    /**
     * Constructor; builds the object from the given post, or from the current post if
     * none is explicitly given.
     *
     * @param  \WP_Post|int|null  $post
     */
    public function __construct($post = null);

    /**
     * Get the list of post types supported by this model.
     *
     * @return  array
     */
    public function getSupportedPostTypes();

    /**
     * Determine whether the object has emitted a \WP_Error.
     *
     * @see  \WP_Error
     *
     * @return  bool
     */
    public function hasErrors();

    /**
     * Get the error object, if one has been generated.
     *
     * @return  \WP_Error|bool
     */
    public function getErrors();
}
