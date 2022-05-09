<?php
/*
 * Copyright 2022 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use ASMBS\ScheduleBuilder\Model\Session;
use WP_Post;
use WP_Query;
use WP_REST_Request;

/**
 * API endpoints for the "session" post_type unique locations.
 *
 * Enable this feature by adding the string "locations" to the environmental `SCHEDULE_BUILDER_API`.
 *
 * - /schedule-builder/locations : obtain an array of the meeting's unique locations
 *
 */
class LocationEndpoint
{

    /**
     * Private constructor. Use the {@see LocationEndpoint::load()} function enable this feature.
     */
    private function __construct()
    {
    }

    /**
     * Loads the rest api routes if `$_ENV['SCHEDULE_BUILDER_API']` contains the string `locations`.
     *
     * @return void
     */
    public static function load()
    {

        // guard to check whether to init the rest api
        if (false === strpos(($_ENV['SCHEDULE_BUILDER_API'] ?? ''), 'locations')) {
            return;
        }


        add_action('rest_api_init', function () {
            // an instance of this endpoint
            $endpoint = new LocationEndpoint();

            register_rest_route('/schedule-builder/', '/locations', [
                'methods' => 'GET',
                'callback' => [$endpoint, 'findAllLocations']
            ]);
        });

    }

    /**
     * Callback for the registered route /schedule-builder/locations which gets all
     * "session" post type unique locations.
     *
     * @param WP_REST_Request $request
     * @return Session[]
     */
    public function findAllLocations(WP_REST_Request $request)
    {

        $args = [
            'post_type' => 'session',
            'status' => 'publish',
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);

        // one-liner to map the session object to an array of unique locations.
        // This needs to be revisited to see if WP_Query support getting this information
        // without having to iterate over every session post type.
        return array_values(
            array_unique(
                array_map(
                    function (WP_Post $post): array {
                        $session = new Session($post);
                        $shortName = $session->getVenueShortname();
                        $room = $session->getRoom();
                        return [
                            'import_id' => str_replace([' ', '{', '}', '(', ')', '/', '\\', '@', ':'], '_', "$shortName.$room"),
                            'name' => "$room, $shortName",
                            'location_type' => 2
                        ];
                    },
                    $query->get_posts()
                ),
                SORT_REGULAR)
        );

    }

}
