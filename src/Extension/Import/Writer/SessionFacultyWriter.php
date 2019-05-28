<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Person;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionFacultyWriter extends SessionWriter {
    /** @var  string */
    protected $groupLabel;

    protected function buildPost( \WP_Post $post, array $item ) {
        return $this;
    }

    public function buildMetaFields( \WP_Post $post, array $item ) {
        // Set label reference
        $this->groupLabel = $item['label'];

        // Get existing groups
        $groups = $this->getExistingGroups( $post );
        $groups = array_filter( $groups, [ $this, 'removeExistingGroups' ] );

        // Find corresponding people
        $people = $this->findPostsWithMeta( Person::SLUG, [
            [
                'key'     => 'person_id',
                'compare' => 'IN',
                'value'   => $item['person_ids'],
            ],
        ], true );
        $people = count( $people ) > 0 ? array_map( [ $this, 'getPostID' ], $people ) : null;

        // Add the group
        $groups[] = [
            'label'  => $item['label'],
            'people' => $people,
        ];

        $this->addMeta( 'faculty--groups', $groups );

        return $this;
    }

    protected function buildTerms( \WP_Post $post, array $item ) {
        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Load existing faculty groups if available.
     *
     * @param \WP_Post $post
     *
     * @return  array
     */
    protected function getExistingGroups( \WP_Post $post ) {
        $groups = get_field( 'faculty--groups', $post->ID );

        return is_array( $groups ) ? $groups : [];
    }

    /**
     * Filter out any groups with the same label as the current import item.
     *
     * @param array $group
     *
     * @return  bool
     */
    protected function removeExistingGroups( array $group ) {
        if ( isset( $group['label'] ) && $group['label'] == $this->groupLabel ) {
            return false;
        }

        return true;
    }
}
