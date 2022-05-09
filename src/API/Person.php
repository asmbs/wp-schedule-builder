<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

class Person extends Session
{

    public static function fromPostId(int $id): ?Person
    {
        if(null === $post = get_post($id)) {
            return null;
        }
        return new Person($post);
    }

    public function jsonSerialize()
    {
        return array_filter([
            '@id' => "person/{$this->post->ID}",
            '@type' => $this->post->post_type,
            'import_id' => "person_{$this->post->ID}",
            'prefix' => $this->postMetadata['prefix']['value'] ?? null,
            'first_name' => html_entity_decode(trim($this->postMetadata['first']['value'] ?? '')),
            'middle_name' => html_entity_decode(trim($this->postMetadata['mi']['value'] ?? '')),
            'last_name' => html_entity_decode(trim($this->postMetadata['last']['value'] ?? '')),
            'suffix' => html_entity_decode(trim($this->postMetadata['suffix']['value'] ?? '')),
            'title' => $this->postMetadata['title']['value'] ?? '',
            'credentials' => $this->postMetadata['credentials']['value'] ?? '',
            'member_id' => intval($this->postMetadata['person_id']['value'] ?? ''),
            'organization' => $this->postMetadata['organization']['value'] ?? '',
            'city' => $this->postMetadata['city']['value'] ?? '',
            'state' => $this->postMetadata['state']['value'] ?? '',
            'country' => $this->postMetadata['country']['value'] ?? '',
            'bio' => $this->postMetadata['bio']['value'] ?? ''
            //'photo' => ''
        ]);
    }

}
