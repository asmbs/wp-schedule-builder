<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\API;

use ASMBS\ScheduleBuilder\Util\Timezones;

abstract class AbstractPost implements \JsonSerializable
{

    private static ?array $optionMetadata = null;

    protected \WP_Post $post;
    protected array $postMetadata;

    protected function __construct(\WP_Post $post)
    {
        $this->post = $post;
        $this->postMetadata = get_field_objects($post->ID);
    }

    public static function getOptionMetadata(): array
    {
        if(null === self::$optionMetadata) {
            self::$optionMetadata = acf_get_meta('sb_options');
        }

        return self::$optionMetadata;
    }

    protected static function findMetaKeyForValue($value): ?string
    {
        if(false === $optionKey = array_search($value, self::getOptionMetadata(), true)) {
            return null;
        }
        return $optionKey;
    }

    public static function createDateTime(?string $date = null, ?string $time = null): ?\DateTimeInterface
    {
        if(null === $date || null == $time ) {
            return null;
        }
        if(false === $theDate = \DateTimeImmutable::createFromFormat('Y/m/d H:i', "$date $time", Timezones::getTimezone())) {
            return null;
        }
        return $theDate;
    }

}
