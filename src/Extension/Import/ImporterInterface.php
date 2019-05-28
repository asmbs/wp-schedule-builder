<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
interface ImporterInterface {
    const SLUG = '_importer';

    /**
     * @return  ImporterInterface
     */
    public static function load();

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Return the label used in the admin menu.
     *
     * @return  string
     */
    public function getMenuTitle();

    /**
     * Return the page title used when rendering the admin page.
     *
     * @return  string
     */
    public function getPageTitle();

    /**
     * Return the post type that this importer should be a submenu item of.
     *
     * @return  string
     */
    public function getPostType();

    /**
     * Return the names of the columns that the import should be mapped to.
     * This essentially defines the schema of the import.
     *
     * @return  string[]
     */
    public function getColumns();

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Process an uploaded file. Parameter MUST be a file from the $_FILES array.
     *
     * @param array $file
     *
     * @return  \SplFileInfo
     */
    public function handleUpload( array $file );

    /**
     * Run the workflow to process the data in an uploaded file.
     *
     * @param \SplFileInfo $file
     * @param bool $replace
     */
    public function processFile( \SplFileInfo $file, $replace = false );

    /**
     * @param string $message
     * @param string $context
     *
     * @return  $this
     */
    public function addNotice( $message, $context );

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Register the admin page.
     */
    public function register();

    /**
     * Render the admin page.
     */
    public function renderPage();
}
