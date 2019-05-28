<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class FacultyWriter extends AbstractPostWriter {
    protected function queryPosts( array $item ) {
        return get_posts( [
            'post_type'   => $this->getPostType(),
            'post_status' => 'any',
            'meta_query'  => [
                [
                    'key'   => $this->getIDKey(),
                    'value' => $item['id'],
                ],
            ],
        ] );
    }

    protected function buildPost( \WP_Post $post, array $item ) {
        return parent::buildPost( $post, $item );
    }

    protected function buildMetaFields( \WP_Post $post, array $item ) {
        $this->addMeta( $this->getIDKey(), $item['id'] )
             ->addMeta( 'prefix', $item['prefix'] )
             ->addMeta( 'first', $item['first'] )
             ->addMeta( 'mi', $item['mi'] )
             ->addMeta( 'last', $item['last'] )
             ->addMeta( 'suffix', $item['suffix'] )
             ->addMeta( 'credentials', $item['credentials'] )
             ->addMeta( 'organization', $item['organization'] )
             ->addMeta( 'title', $item['title'] )
             ->addMeta( 'city', $item['city'] )
             ->addMeta( 'state', $item['state'] )
             ->addMeta( 'country', $item['country'] )
             ->addMeta( 'bio', $item['bio'] )
             ->addAttachment( 'photo', $item['photo'], $item['id'] );

        return $this;
    }

    protected function buildTerms( \WP_Post $post, array $item ) {
        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get the identifier meta key.
     *
     * @return  string
     */
    abstract protected function getIDKey();
}
