<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

class Session extends AbstractPost
{

    public static function fromPostId(int $id): ?AbstractPost
    {
        if(null === $post = get_post($id)) {
            return null;
        }
        return new Session($post);
    }

    private function getContent(): string
    {
        return html_entity_decode(
            str_replace(
                ["\n","\r"],
                '',
                apply_filters(
                    'sb/session_content',
                    apply_filters(
                        'the_content',
                        $this->post->post_content
                    )
                )
            )
        );
    }

    private function societies(): ?array
    {
        $societies = $this->postMetadata['society']['value'];

        if(empty($societies)) {
            return null;
        }

        return array_map(
            fn(int $society): array => [
                    '@id' => "options/society/$society",
                    'import_id' => "options_society_$society"
                ],
            $societies
        );

    }

    private function facultyGroups(): ?array {
        if(null === $facultyGroups = ($this->postMetadata['faculty_groups']['value'] ?? null)) {
            return null;
        }

        return array_map(
            function(array $facultyGroup): array {
                return [
                    'label' => $facultyGroup['label'],
                    'people' => array_map(
                        fn(\WP_Post $person): array => ['@id' => "person/{$person->ID}", 'import_id' => "person_{$person->ID}"],
                        ($facultyGroup['people']) ? $facultyGroup['people'] : [])
                ];
            },
            $facultyGroups
        );
    }

    private function creditTypes(): ?array {
        if(null === $creditTypes = ($this->postMetadata['credit_types']['value'] ?? null)) {
            return null;
        }
        return array_map(
            function(string $type): array {
                $optionKey = self::findMetaKeyForValue($type);
                $offset = [];
                preg_match("/\\d+/", $optionKey, $offset);
                return [
                    '@id' => "options/credit-type/{$offset[0]}",
                    'import_id' => "options_credit_type_{$offset[0]}",
                    'value' => $type
                ];
            },
            $creditTypes);
    }

    private function agendItems(int $sessionId): ?array
    {
        $agendaItems = [];
        foreach($this->postMetadata['agenda_items']['value'] ?? [] as $key=>$value) {
            $agendaItems[] = [
                '@id' => "session/{$sessionId}/agenda-item/$key",
                'import_id' => "session_{$sessionId}_agenda_item_$key"
            ];
        }
        return empty($agendaItems) ? null : $agendaItems;
    }

    private function findVenueObject(): ?array
    {
        if(false === $data = get_field_object('logistics--locations', 'sb_options')) {
            return null;
        }

        //return $data;

        $venueName = $this->postMetadata['venue']['value'] ?? '';
        $roomName = $this->postMetadata['room']['value'] ?? '';

        $obj = [
            '@id' => null,
            'name' => $venueName,
            'shortname' => null,
        ];

        foreach($data['value'] as $key=>$venue) {
            if($venue['location_name'] !== $venueName) {
                continue;
            }
            $obj['@id'] = 'options/venue/' . $key;
            $obj['import_id'] = "options_venue_{$key}";
            $obj['shortname'] = $venue['location_shortname'];
            foreach($venue['location_rooms'] as $offset=>$room) {
                if($roomName !== $room['room_name']) {
                    continue;
                }
                $obj['room'] = [
                    '@id' => "options/venue/$key/room/$offset",
                    'import_id' => "options_venue_{$key}_room_{$offset}",
                    'name' => $roomName
                ];
            }
        }

        return $obj;
    }


    public function jsonSerialize()
    {
        $startTime = $this->createDateTime(
            $this->postMetadata['date']['value'] ?? null,
            $this->postMetadata['start_time']['value'] ?? null);

        $endTime = $this->createDateTime(
            $this->postMetadata['date']['value'] ?? null,
            $this->postMetadata['end_time']['value'] ?? null);

        return array_filter([
            '@id' => "{$this->post->post_type}/{$this->post->ID}",
            '@type' => $this->post->post_type,
            'import_id' => "{$this->post->post_type}_{$this->post->ID}",
            'name' => html_entity_decode($this->post->post_title),
            'description_html' =>  $this->getContent(),
            'start_time' => null !== $startTime ? $startTime->format('Y-m-d\TH:i:s.vO') : null,
            'end_time' => null !== $endTime ? $endTime->format('Y-m-d\TH:i:s.vO') : null,
            'credits' => isset($this->postMetadata['credits_available']) ? floatval($this->postMetadata['credits_available']['value']) : null,
            'credit_types' => $this->creditTypes(),
            'faculty' => $this->facultyGroups(),
            'societies' => $this->societies(),
            'location' => $this->findVenueObject(),
            'agenda_items' => $this->agendItems($this->post->ID)
        ]);
    }
}
