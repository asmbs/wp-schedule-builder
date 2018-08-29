<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\ResearchAbstract;
use ASMBS\ScheduleBuilder\PostType\Person;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionAgendaWriter extends SessionWriter
{
    /** @var  string */
    protected $startTime;

    /** @var  string */
    protected $endTime;

    protected function buildPost(\WP_Post $post, array $item)
    {
        return $this;
    }

    protected function buildMetaFields(\WP_Post $post, array $item)
    {
        // Set time references
        $this->startTime = $item['start_time'];
        $this->endTime = $item['end_time'];
        
        // Load the existing agenda if there is one
        $agenda = $this->getExistingAgenda($post);
        if (count($agenda) > 0) {
            // Filter out any items with the current item's time slot
            $agenda = array_filter($agenda, [$this, 'removeMatchingItems']);
        }

        $agenda[] = $this->buildAgendaItem($post, $item);
        $this->addMeta('agenda--items', $agenda);

        return $this;
    }

    protected function buildTerms(\WP_Post $post, array $item)
    {
        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Load existing agenda items if available, or return an empty array.
     *
     * @param   \WP_Post  $post
     * @return  array
     */
    protected function getExistingAgenda(\WP_Post $post)
    {
        $agenda = get_field('agenda_items', $post->ID);

        return is_array($agenda) ? $agenda : [];
    }

    /**
     * Array filter that removes any agenda items with the same time slot as the
     * current import item.
     *
     * @param   array  $agendaItem
     * @return  bool
     */
    protected function removeMatchingItems(array $agendaItem)
    {
        if ($this->startTime && $this->endTime && isset($agendaItem['start_time']) && isset($agendaItem['end_time'])) {
            if ($agendaItem['start_time'] == $this->startTime && $agendaItem['end_time'] == $this->endTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build an agenda item layout array.
     *
     * @param   \WP_Post  $post
     * @param   array     $item
     * @return  array
     */
    protected function buildAgendaItem(\WP_Post $post, array $item)
    {
        $agendaItem = [
            'acf_fc_layout' => $item['type'],
            'start_time'    => $item['start_time'],
            'end_time'      => $item['end_time'],
        ];

        if ($item['type'] === 'item_talk') {
            // If a person ID was given, try to find the corresponding post
            $speaker = $this->findPostsWithMeta(Person::SLUG, [
                [
                    'key'   => 'person_id',
                    'value' => $item['speaker_id'],
                ],
            ], false);

            $speakerID = $speaker ? $this->getPostID($speaker) : null;

            $agendaItem = array_merge($agendaItem, [
                'talk_title' => $item['talk_title'],
                'speaker'    => $speakerID,
            ]);
        } elseif ($item['type'] === 'item_abstract') {
            // Find the abstract
            $abstract = $this->findPostsWithMeta(ResearchAbstract::SLUG, [
                [
                    'key'   => 'abstract_id',
                    'value' => $item['abstract_id'],
                ],
            ], false);
            $abstract = $abstract ? $this->getPostID($abstract) : null;

            // Find the presenter
            $presenter = $this->findPostsWithMeta(Person::SLUG, [
                [
                    'key'   => 'person_id',
                    'value' => $item['presenter_id'],
                ],
            ], false);
            $presenter = $presenter ? $this->getPostID($presenter) : null;

            // Find discussants
            $discussants = $this->findPostsWithMeta(Person::SLUG, [
                [
                    'key'     => 'person_id',
                    'compare' => 'IN',
                    'value'   => $item['discussant_ids'],
                ],
            ], true);
            $discussants = count($discussants) > 0 ? array_map([$this, 'getPostID'], $discussants) : null;

            $agendaItem = array_merge($agendaItem, [
                'abstract'    => $abstract,
                'presenter'   => $presenter,
                'discussants' => $discussants,
            ]);
        }

        return $agendaItem;
    }
}
