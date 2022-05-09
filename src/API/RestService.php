<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use ASMBS\ScheduleBuilder\Util\Timezones;
use JsonSerializable;
use ReflectionException;
use WP_REST_Request;
use WP_REST_Response;

/**
 * API endpoints for the "session" post_type.
 */
class RestService
{
    /**
     * Private constructor. Use the {@see RestService::load()} function to enable this feature.
     */
    private function __construct()
    {
    }

    /**
     * Loads the rest api routes if `$_ENV['SCHEDULE_BUILDER_API']`.
     *
     * @return void
     */
    public static function load()
    {

        add_action('rest_api_init', function () {

            // singleton instance
            $endpoint = new RestService();

            // match schedule-builder/(sessions|abstracts|people)
            register_rest_route(
                '/schedule-builder/',
                '/(?P<post_type>(sessions|abstracts|people))',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request) use ($endpoint): WP_REST_Response {
                        $posts = $endpoint->findAll($request['post_type'], $request['limit'] ?? null, $request['offset'] ?? null);
                        return new WP_REST_Response($posts);
                    }
                ]
            );

            // match schedule-builder/(session|abstract|person)/[\d]+
            register_rest_route(
                '/schedule-builder/',
                '/(?P<post_type>(session|abstract|person))/(?P<post_id>[\d]+)',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request) use ($endpoint): WP_REST_Response {
                        $status = 200;
                        if (null === $post = $endpoint->findById($request['post_type'], intval($request['post_id']))) {
                            $status = 404;
                        }
                        return new WP_REST_Response($post, $status);
                    }
                ]
            );

            // match schedule-builder/(session)/[\d]+/agenda-item/[\d+]
            register_rest_route(
                '/schedule-builder/',
                '/session/(?P<session>[\d]+)/agenda-item/(?P<id>[\d]+)',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request): WP_REST_Response {
                        $status = 200;
                        if (null === $item = AgendaItem::fromSession(intval($request['session']), intval($request['id']))) {
                            $status = 404;
                        }
                        return new WP_REST_Response($item, $status);
                    }
                ]
            );

            // match schedule-builder/options/(accreditation-statement|credit-types|venues|timezone)
            register_rest_route(
                '/schedule-builder/',
                '/options/(?P<selector>(accreditation-statement|credit-types|venues|timezone))',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request) use ($endpoint): WP_REST_Response {
                        $response = new WP_REST_Response();
                        $method = str_replace('-', '', $request['selector']);
                        call_user_func([$endpoint, $method], $response);
                        return $response;
                    }
                ]
            );


            // match schedule-builder/options/credit-type/[\d]+
            register_rest_route(
                '/schedule-builder/',
                '/options/credit-type/(?P<id>[\d]+)',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request) use ($endpoint): WP_REST_Response {
                        $creditTypes = $endpoint->getCreditTypes();
                        $response = new WP_REST_Response();
                        if(null === $data = ($creditTypes['values'][intval($request['id'])] ?? null)) {
                            $response->set_status(404);
                        }
                        $response->set_data($data);
                        return $response;
                    }
                ]
            );

            // match schedule-builder/options/venue/[\d]+
            register_rest_route(
                '/schedule-builder/',
                '/options/venue/(?P<id>[\d]+)',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request) use ($endpoint): WP_REST_Response {
                        $venues = $endpoint->getVenues();
                        $response = new WP_REST_Response();
                        if(null === $data = ($venues['values'][intval($request['id'])] ?? null)) {
                            $response->set_status(404);
                        }
                        $response->set_data($data);
                        return $response;
                    }
                ]
            );

            // match schedule-builder/options/venue/[\d]+/room/[\d]+
            register_rest_route(
                '/schedule-builder/',
                '/options/venue/(?P<venue>[\d]+)/room/(?P<room>[\d]+)',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request) use ($endpoint): WP_REST_Response {
                        $venues = $endpoint->getVenues();
                        $response = new WP_REST_Response();
                        if(null === $data = ($venues['values'][intval($request['venue'])]['rooms'][intval($request['room'])] ?? null)) {
                            $response->set_status(404);
                        }
                        $response->set_data($data);
                        return $response;
                    }
                ]
            );

            // match schedule-builder/options/venue/[\d]+/room/[\d]+
            register_rest_route(
                '/schedule-builder/',
                '/options/timezone/(?P<tz>(edt|et|est|cdt|ct|cst|mdt|mst|pdt|pst|akdt|akst|hdt|hst))',
                [
                    'methods' => 'GET',
                    'callback' => function (WP_REST_Request $request): WP_REST_Response {
                        return new WP_REST_Response(Timezones::ZONES[$request['tz']]);
                    }
                ]
            );

            register_rest_route(
                '/schedule-builder/',
                '/options/societies',
                [
                    'methods' => 'GET',
                    'callback' => function(WP_REST_Request $request) use ($endpoint) : WP_REST_Response {
                        return new WP_REST_Response($endpoint->getSocieties());
                    }
                ]
            );

            register_rest_route(
                '/schedule-builder/',
                '/options/society/(?P<id>[\d]+)',
                [
                    'methods' => 'GET',
                    'callback' => function(WP_REST_Request $request) use ($endpoint): WP_REST_Response {
                         $id = intval($request['id']);
                         $value = null;
                         $status = 404;
                        foreach($endpoint->getSocieties() as $society) {
                            if("options/society/$id" === $society['@id']) {
                                $value = $society;
                                $status = 200;
                                break;
                            }
                        }

                        return new WP_REST_Response($value, $status);
                    }
                ]
            );

        });
    }

    public function timezone(WP_REST_Response $response): void
    {
        if(null === $data = $this->getTimezone()) {
            $response->set_status(404);
            return;
        }
        $response->set_data($data);
    }

    public function venues(WP_REST_Response $response): void
    {
        if(null === $data = $this->getVenues()) {
            $response->set_status(404);
            return;
        }
        $response->set_data($data);
    }

    public function creditTypes(WP_REST_Response $response): void
    {
        if (null === $data = $this->getCreditTypes()) {
            $response->set_status(404);
            return;
        }
        $response->set_data($data);
    }

    public function getSocieties(): array
    {
        $terms = get_terms('society');
        return array_map(fn(\WP_Term $term) => array_filter([
            '@id' => "options/society/{$term->term_taxonomy_id}",
            '@type' => $term->taxonomy,
            'import_id' => "options/society_{$term->term_taxonomy_id}",
            'name' => $term->name,
            'description' => $term->description
        ]), $terms);

    }

    public function accreditationStatement(WP_REST_Response $response): void
    {
        if (false === $field = get_field_object('accreditation--statement', 'sb_options')) {
            $response->set_status(404);
            return;
        }
        $response->set_data([
            '@id' => 'options/accreditation-statement',
            'import_id' => 'options_accreditation-statement',
            'label' => $field['label'],
            'value' => $field['value']
        ]);
    }

    public function getTimezone(): ?array
    {
        if (false === $data = get_field_object('timezone--select', 'sb_options')) {
            return null;
        }
        $tz = constant(Timezones::class . '::' . $data['default_value']);
        if(defined(Timezones::class . '::' . $data['value'])) {
           $tz = constant(Timezones::class . '::' . $data['value']);
        }

        return [
            '@id' => 'options/timezone',
            'label' => $data['label'],
            'value' => Timezones::ZONES[$tz]
        ];
    }

    public function getVenues(): ?array
    {
        if(false === $data = get_field_object('logistics--locations', 'sb_options')) {
            return null;
        }

        $values = [];
        foreach($data['value'] as $offset=>$venue) {
            $rooms = [];
            foreach($venue['location_rooms'] as $key=>$room) {
                $rooms[] = [
                    '@id' => "options/venue/$offset/room/$key",
                    '@parent' => "options/venue/$offset",
                    'import_id' => "options_location_{$offset}_room_{$key}",
                    'name' => $room['room_name']
                ];
            }

            $values[] = [
                '@id' => "options/venue/$offset",
                'import_id' => "options_location_$offset",
                'name' => $venue['location_name'],
                'shortname' => $venue['location_shortname'],
                'rooms' => $rooms
            ];
        }

        return [
            '@id' => 'options/venues',
            'label' => $data['label'],
            'values' => $values
        ];
    }

    public function getCreditTypes(): ?array
    {
        if (false === $field = get_field_object('accreditation--types', 'sb_options')) {
            return null;
        }

        $values = [];
        foreach ($field['value'] as $offset => $value) {
            $values[] = [
                '@id' => "options/credit-type/$offset",
                'import_id' => "options_credit-type_$offset",
                'value' => $value['type']
            ];
        };

        return [
            '@id' => 'options/credit-types',
            'label' => $field['label'],
            'values' => $values
        ];
    }

    /**
     * Callback for the registered route /schedule-builder/sessions/(?P<post_id>[\d]+) which should
     * get a "session" by its unique post_id.
     *
     * @param string $postType
     * @param int $postId
     * @return JsonSerializable
     */
    public function findById(string $postType, int $postId): ?JsonSerializable
    {
        if('session' === $postType) {
            return Session::fromPostId($postId);
        }

        if('abstract' === $postType) {
            return ResearchAbstract::fromPostId($postId);
        }

        if('person' === $postType) {
            return Person::fromPostId($postId);
        }

        return null;
    }


    /**
     * Callback for the registered route /schedule-builder/sessions which should get all
     * "session" post types.
     *
     * The following query parameters are allowed:
     *  - <strong>limit</strong>: to limit the number of results returned; -1 for everything (default: -1)
     *  - <strong>offset</strong>: number of post to displace or pass over; -1 disabled (default: -1)
     *
     * @param string $postType
     * @param int|null $limit
     * @param int|null $offset
     * @return JsonSerializable[]
     * @throws ReflectionException
     */
    public function findAll(string $postType, ?int $limit = null, ?int $offset = null): array
    {
        $args = [
            'post_type' => substr($postType, 0, -1),   // un-pluralize
            'status' => 'publish',                                  // only published posts
            'posts_per_page' => $limit ?? -1,
            'offset' => $offset ?? -1
        ];

        $query = new \WP_Query($args);

        return array_map(
            fn(\WP_Post $post) => ['@id' => "{$post->post_type}/{$post->ID}", 'import_id' => "{$post->post_type}_{$post->ID}"],
            $query->get_posts()
        );
    }
}
