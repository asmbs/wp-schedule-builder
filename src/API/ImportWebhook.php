<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

class ImportWebhook
{

    private function __construct()
    {
    }

    public static function load(): void
    {
        if(!isset($_ENV['GUIDEBOOK_WEBHOOK_URL'])) {
            return;
        }

        if(!isset($_ENV['GUIDEBOOK_WEBHOOK_TOKEN'])) {
            error_log("Please provide the GUIDEBOOK_WEBHOOK_TOKEN environment variable.");
            return;
        }
        error_log($_ENV['GUIDEBOOK_WEBHOOK_URL']);

        $instance = new ImportWebhook();

        add_action('save_post_person', [$instance, 'notifyPostSaved'], 1000000, 3);
        add_action('save_post_abstract', [$instance, 'notifyPostSaved'], 1000000, 3);
        add_action('save_post_session', [$instance, 'notifyPostSaved'], 1000000, 3);

    }

    final public function notifyPostSaved(int $postId, \WP_Post $post, bool $update) {
        $this->notify([
            '@type' => $post->post_type,
            '@id' => "{$post->post_type}/{$post->ID}",
            'import_id' => "{$post->post_type}_{$post->ID}",
            'update' => $update,
            'status' => $post->post_status
        ]);
    }


    private function notify(array $notice): void
    {
        $response = wp_remote_post(
            $_ENV['GUIDEBOOK_WEBHOOK_URL'],
            [
                'method' => 'POST',
                'headers' => ['Authorization' => 'Bearer ' .  $_ENV['GUIDEBOOK_WEBHOOK_TOKEN']],
                'body' => json_encode($notice)
            ]
        );

        if($response instanceof \WP_Error) {
            error_log($response->get_error_message());
            return;
        }

        $data = json_decode($response['body'], true);

        if(200 !== $data['status']) {
            error_log(sprintf("Guidebook webhook: %d; %s", $data['status'], $data['message']));
        }
    }

}
