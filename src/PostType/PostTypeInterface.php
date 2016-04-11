<?php

namespace ASMBS\ScheduleBuilder\PostType;

/**
 * This interface defines a wrapper for the custom post type API.
 * 
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
interface PostTypeInterface
{
    /** The singular post type slug. */
    const SLUG = '_unknown';

    /**
     * @return  PostTypeInterface
     */
    public static function load();

    /**
     * Get the primary singular label for the post type.
     *
     * @return  string
     */
    public function getSingularLabel();

    /**
     * Get the primary plural label for the post type.
     *
     * @return  string
     */
    public function getPluralLabel();

    /**
     * Return instance-specific arguments for building the post type.
     *
     * @return  array
     */
    public function getArgs();

    /**
     * Register the post type.
     */
    public function register();

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Check whether the post type exists.
     *
     * @return  bool
     */
    public static function exists();

    /**
     * @return  object
     */
    public static function getDefinitionObject();

    /**
     * @return  object
     */
    public static function getLabelObject();

    /**
     * Get the archive permalink.
     *
     * @return  bool|string
     */
    public static function getArchiveLink();

    /**
     * Determine whether a particular post feature is supported.
     *
     * @param   string  $feature
     * @return  bool
     */
    public static function supports($feature);

    /**
     * Add support for the given feature.
     *
     * @param   string|string[]  $feature
     */
    public static function addSupport($feature);

    /**
     * Remove support for the given feature.
     *
     * @param   string  $feature
     */
    public static function removeSupport($feature);
}
