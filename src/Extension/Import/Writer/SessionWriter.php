<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Session;
use ASMBS\ScheduleBuilder\Taxonomy\SessionKeyword;
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

    protected function buildMetaFields(\WP_Post $post, array $item)
    {
        $this->addMeta('session_id', $item['session_id']);
        
        // Date & time
        $this->addMeta('scheduling--date', $item['date'])
            ->addMeta('scheduling--start', $item['start_time'])
            ->addMeta('scheduling--end', $item['end_time']);

        // Venue & room
        $this->addMeta('location--venue', $item['venue'])
            ->addMeta('location--room', $item['room']);

        // Credits
        $this->addMeta('credits--available', $item['credits_available'])
            ->addMeta('credits--types', $item['credit_types']);

        // Evaluable
        $this->addMeta('session--evaluable', $item['evaluable']);

        return $this;
    }

    protected function buildTerms(\WP_Post $post, array $item)
    {
        $this->terms = [];

        $this->addTerm(SessionType::SLUG, $item['session_type'])
            ->addTerm(Society::SLUG, $item['societies'])
            ->addTerm(SessionTag::SLUG, $item['tags'])
            ->addTerm(SessionKeyword::SLUG, $item['keywords']);

        return $this;
    }
}
