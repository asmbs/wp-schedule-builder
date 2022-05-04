<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use ASMBS\ScheduleBuilder\Model\ResearchAbstract;
use ASMBS\ScheduleBuilder\Model\Session;
use ASMBS\ScheduleBuilder\PostType\Session as SessionType;
use \ASMBS\ScheduleBuilder\PostType\ResearchAbstract as ResearchAbstractType;
use ReflectionClass;
use ReflectionException;
use WP_Error;
use WP_REST_Request;

/**
 * API endpoints for the "session" post_type.
 *
 * Enable this feature by adding the string "sessions" to the environmental `SCHEDULE_BUILDER_API`.
 *
 * - /schedule-builder/(sessions|abstracts) : obtain an array of the meeting's sessions or abstracts
 * - /schedule-builder/(sessions|abstracts)/[\d+]: obtain a meeting session or abstract by its post id
 *
 */
class SessionEndpoint
{

    private const POST_TYPES = [
        SessionType::SLUG_PLURAL => [
            'post_type' => SessionType::SLUG,
            'class' => Session::class
        ],
        ResearchAbstractType::SLUG_PLURAL => [
            'post_type' => ResearchAbstractType::SLUG,
            'class' => ResearchAbstract::class
        ]
    ];

    /**
     * Private constructor. Use the {@see SessionEndpoint::load()} function enable this feature.
     */
    private function __construct()
    {
    }

    /**
     * Loads the rest api routes if `$_ENV['SCHEDULE_BUILDER_API']` contains the string `sessions`.
     *
     * @return void
     */
    public static function load()
    {
        // guard to check whether to init the rest api
        if (false === strpos(($_ENV['SCHEDULE_BUILDER_API'] ?? ''), 'sessions')) {
            return;
        }

        add_action('rest_api_init', function () {
            // instance of this class;
            $endpoint = new SessionEndpoint();


            register_rest_route(
                '/schedule-builder/',
                '/(?P<post_type>('. SessionType::SLUG_PLURAL . '|' . ResearchAbstractType::SLUG_PLURAL . '))/(?P<post_id>[\d]+)',
                [
                    'methods' => 'GET',
                    'callback' => [$endpoint, 'findById'
                ]
            ]);


            register_rest_route(
                '/schedule-builder/',
                '/(?P<post_type>('. SessionType::SLUG_PLURAL . '|' . ResearchAbstractType::SLUG_PLURAL . '))',
                [
                    'methods' => 'GET',
                    'callback' => [$endpoint, 'findAll'
                ]
            ]);
        });
    }

    /**
     * @param WP_REST_Request $request
     * @return array|WP_Error
     * @throws ReflectionException
     */
    public function findAll(WP_REST_Request $request): array
    {
        $type = $request['post_type'] ?? 'nones';

        if(!isset(self::POST_TYPES[$type])) {
            return new WP_Error(sprintf(
                'Expected post type %s or %s, found %s',
                SessionType::SLUG_PLURAL,
                ResearchAbstractType::SLUG_PLURAL,
                $type)
            );
        }

        $class = new ReflectionClass(self::POST_TYPES[$type]['class']);

        $args = [
            'post_type' => self::POST_TYPES[$type]['post_type'],
            'status' => 'publish',
            'posts_per_page' => (int)($request['limit'] ?? -1),
            'offset' => (int)($request['offset'] ?? -1)
        ];

        $query = new \WP_Query($args);

        return array_map(function ($post) use ($class) {
            return $class->newInstance($post);
        }, $query->get_posts());

    }

    /**
     * Callback for the registered route /schedule-builder/{sessions|abstracts}/(?P<post_id>[\d]+) which should
     * get a "session|abstract" by its unique post_id.
     *
     * @param WP_REST_Request $request
     * @return Session[]|WP_Error
     * @throws ReflectionException
     */
    public function findById(WP_REST_Request $request)
    {

        $type = $request['post_type'] ?? 'nones';

        if(!isset(self::POST_TYPES[$type])) {
            return new WP_Error(sprintf(
                    'Expected post type %s or %s, found %s',
                    SessionType::SLUG_PLURAL,
                    ResearchAbstractType::SLUG_PLURAL,
                    $type)
            );
        }

        $class = new ReflectionClass(self::POST_TYPES[$type]['class']);

        $post = get_post($request->get_param('post_id'));

        if (null === $post) {
            return new WP_Error('no_posts', __('No post found'), array('status' => 404));
        }

        return [$class->newInstance($post)];
    }
}
