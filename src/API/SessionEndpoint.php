<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use ASMBS\ScheduleBuilder\Model\Session;
use WP_Error;
use WP_REST_Request;

/**
 * API endpoints for the "session" post_type.
 */
class SessionEndpoint
{

    /**
     * Loads the rest api routes if `$_ENV['SCHEDULE_BUILDER_API']` contains the string `sessions`.
     *
     * @return void
     */
    public static function load()
    {
        if (false === strpos(($_ENV['SCHEDULE_BUILDER_API'] ?? ''), 'sessions')) {
            return;
        }

        add_action('rest_api_init', function () {
            $endpoint = new SessionEndpoint();
            register_rest_route('/schedule-builder/', "/sessions", [
                'methods' => 'GET',
                'callback' => [$endpoint, 'findAll']
            ]);

            // match AM22-207
            register_rest_route('/schedule-builder/', "/sessions/(?P<session_id>[a-zA-Z0-9]+-[a-zA-Z0-9]+)", [
                'methods' => 'GET',
                'callback' => [$endpoint, 'findBySessionId']
            ]);

            register_rest_route('/schedule-builder/', "/sessions/(?P<post_id>[\d]+)", [
                'methods' => 'GET',
                'callback' => [$endpoint, 'findByPostId']
            ]);
        });
    }

    /**
     * Callback for the registered route /schedule-builder/sessions/(?P<post_id>[\d]+) which should
     * get a "session" by its unique post_id.
     *
     * @param WP_REST_Request $request
     * @return Session[]|WP_Error
     */
    public function findByPostId(WP_REST_Request $request)
    {

        $post = get_post($request->get_param('post_id'));

        if (null === $post) {
            return new WP_Error('no_posts', __('No post found'), array('status' => 404));
        }

        return [new Session($post)];
    }

    /**
     * Callback for the registered route /schedule-builder/sessions/(?P<session_id>[a-zA-Z0-9]+-[a-zA-Z0-9]+)
     * which should get a "session" by its meta_key `session_id` value.
     *
     * <strong>IMPORTANT</strong> uniqueness is not guaranteed so this may return more than one session.
     *
     * @param WP_REST_Request $request
     * @return Session[]|WP_Error The Session or WP_Error if not found
     */
    public function findBySessionId(WP_REST_Request $request)
    {
        $query = new \WP_Query([
            'post_type' => 'session',
            'status' => 'publish',
            'meta_key' => 'session_id',
            'posts_per_page' => 1,
            'meta_query' => [
                'compare' => '=',
                'key' => 'session_id',
                'type' => 'string',
                'value' => (string)$request->get_param('session_id')
            ]
        ]);

        $session = array_map(function ($post) {
            return new Session($post);
        }, $query->get_posts());
        if (empty($session)) {
            return new WP_Error('no_posts', __('No post found'), array('status' => 404));
        }
        return $session;

    }

    /**
     * Callback for the registered route /schedule-builder/sessions which should get all
     * "session" post types.
     *
     * The following query parameters are allowed:
     *  - <strong>limit</strong>: to limit the number of results returned; -1 for everything (default: -1)
     *  - <strong>offset</strong>: number of post to displace or pass over; -1 disabled (default: -1)
     *
     * @param WP_REST_Request $request
     * @return array
     */
    public function findAll(WP_REST_Request $request): array
    {
        $args = [
            'post_type' => 'session',
            'status' => 'publish',
            'posts_per_page' => (int)($request->get_param('limit') ?? -1),
            'offset' => (int)($request->get_param('offset') ?? -1)
        ];

        $query = new \WP_Query($args);

        return array_map(function ($post) {
            return new Session($post);
        }, $query->get_posts());

    }
}
