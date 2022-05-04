<?php
/*
 * Copyright 2022 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use ASMBS\ScheduleBuilder\Model\Session;
use WP_Query;
use WP_REST_Request;

class FacultyGroupsEndpoint
{

    public static function load() {

        if (false === strpos(($_ENV['SCHEDULE_BUILDER_API'] ?? ''), 'faculty-groups')) {
            return;
        }

        add_action('rest_api_init', function () {
            $endpoint = new FacultyGroupsEndpoint();

            register_rest_route('/schedule-builder/', '/faculty-groups', [
                'methods' => 'GET',
                'callback' => [$endpoint, 'findAllFacultyGroups']
            ]);

        });

    }

    public function findAllFacultyGroups(WP_REST_Request $request) {

        $args = [
            'post_type' => 'session',
            'status' => 'publish',
            'posts_per_page' => -1,
        ];

        $query = new WP_Query($args);


        $faculty = [];
        foreach($query->get_posts() as $post) {
            $session = new Session($post);

            foreach($session->getFacultyGroups() as $facultyGroup) {
                foreach($facultyGroup->getPeople() as $person) {
                    $id = $person->getPostID();
                    if(!isset($faculty[$id])) {
                        $faculty[$id] = [
                            'person' => $id
                        ];
                    }

                    $label = $facultyGroup->getLabel();

                    if(!isset($faculty[$id]['roles'][$label])) {
                        $faculty[$id]['roles'][$label] = [
                            'title' => $label,
                            'sessions' => []
                        ];
                    }

                    $faculty[$id]['roles'][$label]['sessions'][] = $session->getPostID();
                }
            }

        }

        return array_map(
            fn(array $value): array => ['person' => $value['person'], 'roles' => array_values($value['roles'])],
            array_values($faculty));
    }

}
