<?php

/*
 * Copyright 2021 American Society for Metabolic & Bariatric Surgery - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Michael Lucas <michael@asmbs.org>
 */

namespace ASMBS\ScheduleBuilder\Util;

class Timezones
{
    private static ?\DateTimeZone $tz = null;

    //EDT (Eastern Daylight Time  | UTC−04)
    public const EDT = 'edt';
    //ET (Eastern Time  | UTC−05 / UTC−04)
    public const ET = 'et';
    //EST (Eastern Standard Time  | UTC−05)
    public const EST = 'est';
    // "CDT (Central Daylight Time  | UTC−05)"
    public const CDT = 'cdt';
    // CT (Central Time | UTC−06/UTC−05)
    public const CT = 'ct';
    // CST (Central Standard Time  | UTC−06)
    public const CST = 'cst';
    // MDT (Mountain Daylight Time  | UTC−06)
    public const MDT = 'mdt';
    // MST (Mountain Standard Time  | UTC−07)
    public const MST = 'mst';
    // PDT (Pacific Daylight Time  | UTC−07)
    public const PDT = 'pdt';
    // PST (Pacific Standard Time  | UTC−08)
    public const PST = 'pst';
    // AKDT (Alaska Daylight Time | UTC−08)
    public const AKDT = 'akdt';
    // AKST (Alaska Standard Time | UTC−09)
    public const AKST = 'akst';
    // HDT (Hawaii–Aleutian Daylight Time | UTC−09)
    public const HDT = 'hdt';
    // HST (Hawaii–Aleutian Standard Time | UTC−10)
    public const HST = 'hst';

    // the timezone data structures
    public const ZONES = [
        self::EDT => [
            '@id' => 'options/timezones/edt',
            'import_id' => 'options_timezones_edt',
            'label' => '(Eastern Daylight Time | UTC−04)',
            'offset' => '-0400'
        ],
        self::EST => [
            '@id' => 'options/timezones/est',
            'import_id' => 'options_timezones_est',
            'label' => 'EST (Eastern Standard Time | UTC−05)',
            'offset' => '-0500'
        ],
        self::ET => [
            '@id' => 'options/timezones/et',
            'import_id' => 'options_timezones_et',
            'label' => 'ET (Eastern Time | UTC−05)',
            'offset' => '-0500'
        ],
        self::CDT => [
            '@id' => 'options/timezones/cdt',
            'import_id' => 'options_timezones_cdt',
            'label' => 'CDT (Central Daylight Time | UTC−05)',
            'offset' => '-0500'
        ],
        self::CST => [
            '@id' => 'options/timezones/cst',
            'import_id' => 'options_timezones_cst',
            'label' => 'CST (Central Standard Time | UTC−06)',
            'offset' => '-0600'
        ],
        self::CT => [
            '@id' => 'options/timezones/ct',
            'import_id' => 'options_timezones_ct',
            'label' => 'CT (Central Time | UTC−06)',
            'offset' => '-0600'
        ],
        self::MDT => [
            '@id' => 'options/timezones/mdt',
            'import_id' => 'options_timezones_mdt',
            'label' => 'MDT (Mountain Daylight Time | UTC−06)',
            'offset' => '-0600'
        ],
        self::MST => [
            '@id' => 'options/timezones/mst',
            'import_id' => 'options_timezones_mst',
            'label' => 'MST (Mountain Standard Time | UTC−07)',
            'offset' => '-0700'
        ],
        self::PDT => [
            '@id' => 'options/timezones/pdt',
            'import_id' => 'options_timezones_pdt',
            'label' => 'PDT (Pacific Daylight Time | UTC−07)',
            'offset' => '-0700'
        ],
        self::PST => [
            '@id' => 'options/timezones/pst',
            'import_id' => 'options_timezones_pst',
            'label' => 'PST (Pacific Standard Time | UTC−08)',
            'offset' => '-0800'
        ],
        self::AKDT => [
            '@id' => 'options/timezones/akdt',
            'import_id' => 'options_timezones_akdt',
            'label' => 'AKDT (Alaska Daylight Time | UTC−08)',
            'offset' => '-0800'
        ],
        self::AKST => [
            '@id' => 'options/timezones/akst',
            'import_id' => 'options_timezones_akst',
            'label' => 'AKST (Alaska Standard Time | UTC−09)',
            'offset' => '-0900'
        ],
        self::HDT => [
            '@id' => 'options/timezones/hdt',
            'import_id' => 'options_timezones_hdt',
            'label' => 'HDT (Hawaii–Aleutian Daylight Time | UTC−09)',
            'offset' => '-0900'
        ],
        self::HST => [
            '@id' => 'options/timezones/hst',
            'import_id' => 'options_timezones_hst',
            'label' => 'HST (Hawaii–Aleutian Standard Time | UTC−10)',
            'offset' => '-1000'
        ]
    ];

    // lazy load an option value;
    public static function getTimezone(): \DateTimeZone {
        if(null === self::$tz) {
            if (false === $tz = get_field_object('timezone--select', 'sb_options')) {
                self::$tz = new \DateTimeZone(Timezones::ZONES[Timezones::ET]['offset']);
            }
            $tzOffset = strtolower($tz['value']);
            if(!isset(Timezones::ZONES[$tzOffset])) {
                $tzOffset = strtolower($tz['default_value']);
            }
            self::$tz = new \DateTimeZone(Timezones::ZONES[$tzOffset]['offset']);
        }
        return self::$tz;
    }

}
