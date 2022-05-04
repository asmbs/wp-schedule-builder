<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use ASMBS\ScheduleBuilder\Model\Person;
use WP_Error;
use WP_Query;
use WP_REST_Request;

/**
 * API endpoints for the "person" post_type.
 *
 * Enable this feature by adding the string "people" to the environmental `SCHEDULE_BUILDER_API`.
 *
 * - /schedule-builder/people : obtain an array of the meeting's abstracts
 * - /schedule-builder/people/{postId}: obtain a meeting session by its post id
 *
 */
class PeopleEndpoint
{

    /**
     * Private constructor. Use {@see PeopleEndpoint::load()} to enable this feature.
     */
    private function __construct()
    {
    }

    /**
     * Loads the rest api routes if `$_ENV['SCHEDULE_BUILDER_API']` contains the string `people`.
     *
     * @return void
     */
    public static function load()
    {
        // guard to check whether to init the rest api
        if (false === strpos(($_ENV['SCHEDULE_BUILDER_API'] ?? ''), 'people')) {
            return;
        }

        add_action('rest_api_init', function () {
            // instance of this class
            $endpoint = new PeopleEndpoint();

            // add the endpoints
            register_rest_route('/schedule-builder', "/people", [
                'methods' => 'GET',
                'callback' => [$endpoint, 'findAll']
            ]);

            register_rest_route('/schedule-builder', "/people/(?P<post_id>[\d]+)", [
                'methods' => 'GET',
                'callback' => [$endpoint, 'findById']
            ]);
        });
    }

    /**
     * Callback for the registered route /schedule-builder/people/(?P<post_id>[\d]+) which
     * get a "person" by its unique post_id.
     *
     * @param WP_REST_Request $request
     * @return Person[]|WP_Error
     */
    public function findById(WP_REST_Request $request)
    {
        $post = get_post($request->get_param('post_id'));
        if (null === $post) {
            return new WP_Error('no_posts', __('No post found'), array('status' => 404));
        }
        return [new Person($post)];
    }

    /**
     * Callback for the registered route /schedule-builder/people which should get all "person" post
     * types.
     *
     * The following query parameters are allowed:
     *  - <strong>limit</strong>: to limit the number of results returned; -1 for everything (default: -1)
     *  - <strong>offset</strong>: number of post to displace or pass over; -1 disabled (default: -1)
     *
     * @param WP_REST_Request $request
     * @return Person[]
     */
    public function findAll(WP_REST_Request $request): array
    {

        $args = [
            'post_type' => 'person',
            'status' => 'publish',
            'posts_per_page' => (int)($request->get_param('limit') ?? -1),
            'offset' => (int)($request->get_param('offset') ?? -1)
        ];
        $query = new WP_Query($args);

        return array_map(function ($post) {
            return new Person($post);
        },
            $query->get_posts());
    }

}
