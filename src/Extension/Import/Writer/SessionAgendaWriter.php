<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Author;
use ASMBS\ScheduleBuilder\PostType\ResearchAbstract;
use ASMBS\ScheduleBuilder\PostType\Session;
use ASMBS\ScheduleBuilder\PostType\Speaker;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionAgendaWriter extends SessionWriter
{
    /** @var  string */
    protected $startTime;

    /** @var  string */
    protected $endTime;

    protected function buildMetaFields(\WP_Post $post, array $item)
    {
        // Set time references
        $this->startTime = $item['start_time'];
        $this->endTime = $item['end_time'];
        
        // Load the existing agenda items, if any.
        // If there are:
        //   See if there is already an item with the same start end end time.
        //   If yes:
        //     Replace that item with this one.
        // Otherwise:
        //   Add the item to the list.

        // Load the existing agenda if there is one
        $agenda = $this->getExistingAgenda($post);
        if (count($agenda) > 0) {
            // If the agenda isn't empty, check to see if an item in this timeslot exists
            $agenda = array_filter($agenda, [$this, 'removeMatchingItems']);
        }

        $agenda[] = $this->buildAgendaItem($post, $item);
        $this->addMeta('agenda', $agenda);
        
        return $this;
    }

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
        if ($this->startTime && $this->endTime) {
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
        $item = [
            'fc_layout' => $item['type'],
            'start_time' => $item['start_time'],
            'end_time'   => $item['end_time'],
        ];

        if ($item['type'] === 'item_talk') {
            // If a speaker ID was given, try to find the corresponding post
            $speaker = $this->findPostsWithMeta(Speaker::SLUG, [
                [
                    'key'   => 'speaker_id',
                    'value' => $item['speaker_id'],
                ],
            ], false);

            $speakerID = $speaker ? $this->getPostID($speaker) : null;

            $item = array_merge($item, [
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
            $presenter = $this->findPostsWithMeta(Author::SLUG, [
                [
                    'key'   => 'author_id',
                    'value' => $item['presenter_id'],
                ],
            ], true);
            $presenter = $presenter ? $this->getPostID($presenter) : null;

            // Find discussants
            $discussants = $this->findPostsWithMeta(Speaker::SLUG, [
                [
                    'key'     => 'speaker_id',
                    'compare' => 'IN',
                    'value'   => $item['discussant_ids'],
                ],
            ], true);
            $discussants = count($discussants) > 0 ? array_map([$this, 'getPostID'], $discussants) : null;

            $item = array_merge($item, [
                'abstract'    => $abstract,
                'presenter'   => $presenter,
                'discussants' => $discussants,
            ]);
        }

        return $item;
    }
}
