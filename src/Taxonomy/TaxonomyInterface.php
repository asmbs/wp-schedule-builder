<?php

namespace ASMBS\ScheduleBuilder\Taxonomy;


/**
 * This interface defines a wrapper for the custom taxonomy API.
 * 
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
interface TaxonomyInterface
{
    const SLUG = '_taxonomy';

    /**
     * @return  TaxonomyInterface
     */
    public static function load();

    /**
     * Get the singular label for the taxonomy.
     *
     * @return  string
     */
    public function getSingularLabel();

    /**
     * Get the plural label for the taxonomy.
     * @return mixed
     */
    public function getPluralLabel();

    /**
     * List the post types this taxonomy will be applied to.
     *
     * @return  string[]
     */
    public function getPostTypes();

    /**
     * Return instance-specific arguments for building the taxonomy.
     *
     * @return  array
     */
    public function getArgs();

    /**
     * Register the taxonomy.
     */
    public function register();

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Determine whether the taxonomy has been registered.
     *
     * @return  bool
     */
    public static function exists();

    /**
     * Get the taxonomy definition.
     *
     * @return  object
     */
    public static function getDefinition();

    /**
     * Get the labels defined for the taxonomy.
     *
     * @return  object
     */
    public static function getLabels();

    /**
     * Get the archive permalink.
     *
     * @param   object|int|string  $term
     * @return  bool|string
     */
    public static function getArchiveLink($term);
}
