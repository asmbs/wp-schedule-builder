<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use Doctrine\DBAL\Types\DateImmutableType;

class ResearchAbstract extends Session
{

    public static function fromPostId(int $id): ?ResearchAbstract
    {
        if (null === $post = get_post($id)) {
            return null;
        }
        return new ResearchAbstract($post);
    }

    public function jsonSerialize()
    {
        $embargoDate = self::createDateTime($this->postMetadata['embargo_date']['value'], '07:00');
        $isEmbargo = $embargoDate === null || (new \DateTimeImmutable('now')) < $embargoDate;

        $data = array_merge(
            parent::jsonSerialize(),
            [
                'abstract_id' => $this->postMetadata['abstract_id']['value'],
                'name' => $this->postMetadata['title']['value'],
                'authors' => array_map(fn(\WP_Post $author): array => ['@id' => "person/{$author->ID}", 'import_id' => "person_{$author->ID}"], $this->postMetadata['authors']['value']),
                'embargo_date' => null !== $embargoDate ? $embargoDate->format('Y-m-d\TH:i:s.vO') : null,
                'is_embargo' => $isEmbargo
            ]
        );

        if(!$isEmbargo) {
            $data = array_merge($data, [
                'introduction' => html_entity_decode($this->postMetadata['introduction']['value'] ?? ''),
                'methods' => html_entity_decode($this->postMetadata['methods']['value'] ?? ''),
                'results' => html_entity_decode($this->postMetadata['results']['value'] ?? ''),
                'conclusions' => html_entity_decode($this->postMetadata['conclusions']['value']) ?? ''
            ]);
        }

        return array_filter($data);
    }

}
