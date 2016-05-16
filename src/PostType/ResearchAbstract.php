<?php

namespace ASMBS\ScheduleBuilder\PostType;

use ASMBS\ScheduleBuilder\Taxonomy\ResearchAbstractType;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ResearchAbstract extends AbstractPostType
{
    const SLUG = 'abstract';

    /**
     * {@inheritdoc}
     */
    public function getSingularLabel()
    {
        return 'Abstract';
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralLabel()
    {
        return 'Abstracts';
    }

    /**
     * {@inheritdoc}
     */
    public function getArgs()
    {
        return [
            'menu_position'   => 30,
            'menu_icon'       => 'dashicons-media-text',
            'has_archive'     => 'abstracts',
            'supports'        => ['author', 'revisions'],
            'capability_type' => ['abstract', 'abstracts'],
            'map_meta_cap'    => true,
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function __construct()
    {
        parent::__construct();

        add_filter('wp_insert_post_data', [$this, 'syncTitle'], 100, 2);

        add_filter(sprintf('manage_edit-%s_columns', static::SLUG), [$this, 'setPostTableColumns']);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Use ID and title fields to generate the post title on save.
     *
     * @param   array  $newData
     * @param   array  $oldData
     * @return  array
     */
    public function syncTitle($newData, $oldData)
    {
        if ($newData['post_type'] !== static::SLUG) {
            return $newData;
        }

        $ID = isset($oldData['ID']) ? $oldData['ID'] : 0;

        $abstractID = $title = '(none)';

        if (isset($_POST['acf'])) {
            // Use field input for normal edits
            $acf = &$_POST['acf'];
            if (isset($acf['basic--id'])) {
                $abstractID = $acf['basic--id'];
            }
            if (isset($acf['basic--title'])) {
                $title = $acf['basic--title'];
            }
        } elseif ($ID !== 0) {
            // Use existing fields for bulk/quick edits
            $abstractID = get_field('abstract_id', $ID);
            $title = get_field('title', $ID);
        }

        $newData['post_title'] = sprintf('%s | %s', $abstractID, $title);

        return $newData;
    }

    /**
     * Adjust the post table columns.
     *
     * @param   array  $columns
     * @return  array
     */
    public function setPostTableColumns($columns)
    {
        $newColumns = [];
        foreach ($columns as $id => $title) {
            switch ($id) {
                case 'taxonomy-'. ResearchAbstractType::SLUG:
                    break;
                default:
                    $newColumns[$id] = $title;
            }
        }
        $key = 'taxonomy-'. ResearchAbstractType::SLUG;
        if (isset($columns[$key])) {
            $columns[$key] = ResearchAbstractType::getLabels()->singular_name;
        }

        return $columns;
    }
}
