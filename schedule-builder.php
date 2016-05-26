<?php
/**
 * Plugin Name: Schedule Builder
 * Description: Build interactive agendas for scientific meetings.
 * Version:     1.3.4
 * Plugin URI:  https://github.com/asmbs/wp-schedule-builder
 * Author:      The A-TEAM
 * Author URI:  https://github.com/asmbs
 */

if (is_file(__DIR__ .'/vendor/autoload.php')) {
    require __DIR__ .'/vendor/autoload.php';
}

new \ASMBS\ScheduleBuilder\Loader();
