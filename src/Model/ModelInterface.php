<?php

namespace ASMBS\ScheduleBuilder\Model;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
interface ModelInterface extends \JsonSerializable {
    /**
     * Constructor; builds the object from the given post, or from the current post if
     * none is explicitly given.
     *
     * @param \WP_Post|int|null $post
     */
    public function __construct( $post = null );

    /**
     * Get the wrapped post's ID.
     *
     * @return  int
     */
    public function getPostID();

    /**
     * Get the list of post types supported by this model.
     *
     * @return  array
     */
    public function getSupportedPostTypes();

    /**
     * Determine whether the object has emitted a \WP_Error.
     *
     * @return  bool
     * @see  \WP_Error
     *
     */
    public function hasErrors();

    /**
     * Get the error object, if one has been generated.
     *
     * @return  \WP_Error|bool
     */
    public function getErrors();
}
