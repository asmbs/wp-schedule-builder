<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use WP_Post;

class AgendaItem extends AbstractPost
{

    private int $agendaId;
    private array $agendaMetadata;

    /**
     * Private constructor. Use {@see AgendaItem::fromSession()} to obtain an instance of this
     * class.
     *
     * @param WP_Post $post
     * @param int $agendaId
     * @param array $agendaMetadata
     */
    private function __construct(\WP_Post $post, int $agendaId, array $agendaMetadata)
    {
        parent::__construct($post);
        $this->agendaId = $agendaId;
        $this->agendaMetadata = $agendaMetadata;
    }

    /**
     * Static factory method to create an AgendaItem from a session post id and
     * the agenda item id (series).
     *
     * @param int $sessionId
     * @param int $agendaId
     * @return AgendaItem|null
     */
    public static function fromSession(int $sessionId, int $agendaId): ?AgendaItem
    {
        if(null === $post = get_post($sessionId)) {
            return null;
        }

        if(false === $agendaItems = get_field_object('agenda--items', $sessionId)) {
            return null;
        }

        if(null === $agendaItem = ($agendaItems['value'][$agendaId])) {
            return null;
        }

        return new AgendaItem($post, $agendaId, $agendaItem);
    }

    /**
     * Exports the data for an 'abstract' agenda item.
     * @return array
     */
    private function abstractItem(): array
    {

        $item = [
            '@type' => 'abstract'
        ];
        // export the abstract post if exists.
        if($this->agendaMetadata['abstract']) {
            $item['abstract'] = [
                '@id' => "abstract/{$this->agendaMetadata['abstract']->ID}",
                'import_id' => "abstract_{$this->agendaMetadata['abstract']->ID}"
            ];
        }

        // export the presenter if exists
        if($this->agendaMetadata['presenter']) {
            $item['presenter'] = [
                '@id' => "person/{$this->agendaMetadata['presenter']->ID}",
                'import_id' => "person_{$this->agendaMetadata['presenter']->ID}"
            ];
        }

        // export the discussants if exists
        if($this->agendaMetadata['discussants']) {
            $item['discussants'] = array_map(
                // map the discussant posts to an array
                fn(WP_Post $discussant): array => ['@id' => "person/{$discussant->ID}", 'import_id' => "person_{$discussant->ID}"],
                $this->agendaMetadata['discussants']
            );
        }
        return $item;
    }

    /**
     * Exports the data for a talk agenda item
     * @return array
     */
    private function talkItem(): array
    {
        // Talks should have a name
        $item = [
            '@type' => 'talk',
            'name' => html_entity_decode($this->agendaMetadata['talk_title'] ?? ''),
        ];

        // export the talk speakers
        if($this->agendaMetadata['speaker']) {
            $item['speakers'] = array_map(
                // map the person post to an array
                fn(WP_Post $speaker): array => ['@id' => "person/{$speaker->ID}", 'import_id' => "person_{$speaker->ID}"],
                $this->agendaMetadata['speaker']
            );
        }

        return $item;
    }

    private function headerItem(): array
    {
        $item = [
            '@type' => 'header',
            'name' => html_entity_decode($this->agendaMetadata['section_title'] ?? ''),
        ];

        // has faculty
        // has people

        return $item;
    }

    private function simpleItem(): array
    {
        $item = [
            '@type' => 'simple',
            'name' => $this->agendaMetadata['title']
        ];
        return $item;
    }

    private function breakItem(): array
    {
        $item = [
            '@type' => 'break',
            'name' => ucfirst($this->agendaMetadata['break_type']),
            'break_type' => $this->agendaMetadata['break_type']
        ];

        return $item;
    }

    public function jsonSerialize()
    {
        // create the start time from the session post date and agenda item start time
        $startTime = $this->createDateTime(
            $this->postMetadata['date']['value'],
            $this->agendaMetadata['start_time'] ?? '');

        // create the end time from the session post date and agenda item end time
        $endTime = $this->createDateTime(
            $this->postMetadata['date']['value'],
            $this->agendaMetadata['end_time'] ?? '');

        // export common data from the different agenda item types
        $data = [
            '@id' => "session/{$this->post->ID}/agenda-item/{$this->agendaId}",
            'import_id' => "session_{$this->post->ID}_agenda_item_{$this->agendaId}",
            'start_time' => null !== $startTime ? $startTime->format('Y-m-d\TH:i:s.vO') : null,
            'end_time' => null !== $endTime ? $endTime->format('Y-m-d\TH:i:s.vO') : null,
        ];

        $type = $this->agendaMetadata['acf_fc_layout'];
        $itemData = [];
        if('item_abstract' === $type) {     // abstract
            $itemData = $this->abstractItem();
        } else if ('item_talk' === $type) { // talk
            $itemData = $this->talkItem();
        } else if ('item_header' === $type) { // header
            $itemData = $this->headerItem();
        } else if('item_simple' === $type) {
            $itemData = $this->simpleItem();
        } else if ('item_break' === $type) {
            $itemData = $this->breakItem();

        }

        return array_filter(array_merge($data, $itemData));
    }
}
