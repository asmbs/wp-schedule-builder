<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Session;
use ASMBS\ScheduleBuilder\Taxonomy\SessionTag;
use ASMBS\ScheduleBuilder\Taxonomy\SessionType;
use ASMBS\ScheduleBuilder\Taxonomy\Society;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class SessionWriter extends AbstractPostWriter
{
    public function getPostType()
    {
        return Session::SLUG;
    }

    public function queryPosts(array $item)
    {
        return get_posts([
            'post_type'   => $this->getPostType(),
            'post_status' => 'any',
            'meta_query'  => [
                [
                    'key'   => 'session_id',
                    'value' => $item['session_id'],
                ],
            ],
        ]);
    }

    protected function buildPost(\WP_Post $post, array $item)
    {
        parent::buildPost($post, $item);

        $post->post_title = $item['title'];
        $post->post_content = $item['content'];

        return $this;
    }

    protected function buildMetaFields(array $item)
    {
        $this->addMeta('session_id', $item['session_id']);
        
        // Date & time
        $this->addMeta('date', $item['date'])
            ->addMeta('start_time', $item['start_time'])
            ->addMeta('end_time', $item['end_time']);

        // Venue & room
        $this->addMeta('venue', $item['venue'])
            ->addMeta('room', $item['room']);

        // Credits
        $this->addMeta('credits_available', $item['credits_available'])
            ->addMeta('credit_types', $item['credit_types']);

        return $this;
    }

    protected function buildTerms(array $item)
    {
        $this->terms = [];

        $this->addTerm(SessionType::SLUG, $item['session_type'])
            ->addTerm(Society::SLUG, $item['societies'])
            ->addTerm(SessionTag::SLUG, $item['tags']);

        return $this;
    }
}
