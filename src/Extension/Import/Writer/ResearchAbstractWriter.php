<?php

namespace ASMBS\ScheduleBuilder\Extension\Import\Writer;

use ASMBS\ScheduleBuilder\PostType\Author;
use ASMBS\ScheduleBuilder\PostType\ResearchAbstract;
use ASMBS\ScheduleBuilder\Taxonomy\ResearchAbstractKeyword;
use ASMBS\ScheduleBuilder\Taxonomy\ResearchAbstractType;
use ASMBS\ScheduleBuilder\Taxonomy\Society;


/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
class ResearchAbstractWriter extends AbstractPostWriter
{
    public function getPostType()
    {
        return ResearchAbstract::SLUG;
    }

    public function queryPosts(array $item)
    {
        return get_posts([
            'post_type'   => $this->getPostType(),
            'post_status' => 'any',
            'meta_query'  => [
                [
                    'key'   => 'abstract_id',
                    'value' => $item['abstract_id'],
                ],
            ],
        ]);
    }

    public function buildPost(\WP_Post $post, array $item)
    {
        parent::buildPost($post, $item);

        return $this;
    }

    public function buildMetaFields(\WP_Post $post, array $item)
    {
        // Add fields
        $this->addMeta('abstract_id', $item['abstract_id'])
            ->addMeta('title', $item['title'])
            ->addMeta('introduction', $item['introduction'])
            ->addMeta('methods', $item['methods'])
            ->addMeta('results', $item['results'])
            ->addMeta('conclusions', $item['conclusions'])
            ->addMeta('embargo_date', $item['embargo_date']);

        // Find authors matching the given IDs
        $authors = $this->findPostsWithMeta(Author::SLUG, [
            [
                'key'     => 'author_id',
                'compare' => 'IN',
                'value'   => $item['author_ids'],
                'posts_per_page' => -1,
            ],
        ], true);

        if (count($authors) > 0) {
            $this->addMeta('authors', array_map([$this, 'getPostID'], $authors));
        }

        return $this;
    }

    public function buildTerms(\WP_Post $post, array $item)
    {
        $this->addTerm(ResearchAbstractType::SLUG, $item['type'])
            ->addTerm(Society::SLUG, $item['societies'])
            ->addTerm(ResearchAbstractKeyword::SLUG, $item['keywords']);

        return $this;
    }
}
