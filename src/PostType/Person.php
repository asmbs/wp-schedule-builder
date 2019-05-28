<?php

namespace ASMBS\ScheduleBuilder\PostType;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 * @author  James Osterhout <jrosterhout@gmail.com>
 */
class Person extends AbstractPostType {

    const SLUG = 'person';
    const SLUG_PLURAL = 'people';

    /**
     * {@inheritdoc}
     */
    public function getSingularLabel() {
        return 'Person';
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralLabel() {
        return 'People';
    }

    public function getArgs() {
        return [
            'labels'          => [
                'menu_name'      => 'People',
                'name'           => 'People',
                'singular_name'  => 'Person',
                'menu_admin_bar' => 'People',
                'view_item'      => sprintf( 'View %s', 'People' ),
            ],
            'menu_position'   => 29,
            'menu_icon'       => 'dashicons-id',
            'has_archive'     => 'people',
            'supports'        => [ 'title', 'author', 'revisions' ],
            'capability_type' => [ 'person', 'people' ],
            'map_meta_cap'    => true,
        ];
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function __construct() {
        parent::__construct();

        add_filter( 'wp_insert_post_data', [ $this, 'syncTitle' ], 100, 2 );
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Use name fields to generate the post title whenever it's saved.
     *
     * @param array $newData
     * @param array $oldData
     *
     * @return  array
     */
    public function syncTitle( $newData, $oldData ) {
        if ( $newData['post_type'] !== static::SLUG ) {
            // Stop processing if post type doesn't match
            return $newData;
        }

        $ID = isset( $oldData['ID'] ) ? $oldData['ID'] : 0;

        $first = $last = '(none)';
        $mi    = $suffix = $credentials = null;

        if ( isset( $_POST['acf'] ) ) {
            foreach ( $_POST['acf'] as $key => $value ) {
                switch ( $key ) {
                    case 'name--first':
                        $first = $value;
                        break;
                    case 'name--mi':
                        $mi = $value;
                        break;
                    case 'name--last':
                        $last = $value;
                        break;
                    case 'name--suffix':
                        $suffix = $value;
                        break;
                    case 'name--credentials':
                        $credentials = $value;
                        break;
                }
            }
        } elseif ( $ID !== 0 ) {
            $first       = get_field( 'first', $ID );
            $mi          = get_field( 'mi', $ID );
            $last        = get_field( 'last', $ID );
            $suffix      = get_field( 'suffix', $ID );
            $credentials = get_field( 'credentials', $ID );
        }

        // Build format string
        $format = '%3$s, %1$s'; // {last}, {first}...
        if ( $mi ) {
            $format .= ' %2$s';
        }
        if ( $suffix ) {
            $format .= ', %4$s';
        }
        if ( $credentials ) {
            $format .= ' | %5$s';
        }

        // Update the title
        $newData['post_title'] = sprintf(
            $format,
            $first,
            $mi,
            $last,
            $suffix,
            $credentials
        );

        // Update the slug
        $newData['post_name'] = '';

        return $newData;
    }
}
