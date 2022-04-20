# ScheduleBuilder

> ScheduleBuilder is a WordPress plugin for building interactive agendas for scientific meetings.

[![Latest Stable Version](https://poser.pugx.org/asmbs/wp-schedule-builder/v/stable)](https://packagist.org/packages/asmbs/wp-schedule-builder)[![Latest Unstable Version](https://poser.pugx.org/asmbs/wp-schedule-builder/v/unstable)](https://packagist.org/packages/asmbs/wp-schedule-builder)



## Requirements

- PHP 7.0+
- A Composer-based WordPress stack like [Bedrock](https://github.com/roots/bedrock)
- [Node + NPM](https://nodejs.org)
- The [Advanced Custom Fields (ACF) Pro](https://www.advancedcustomfields.com/pro/) WordPress plugin v5.7+



## Installation

1. Install with Composer:

    ```
    composer require asmbs/wp-schedule-builder
    ```
    
2. Activate the plugin.
3. Go to the newly created **Schedule Settings** page and add the conference dates, venues, rooms and credit information for the event you're managing.
4. Start building!



## Development

#### Requirements

- NPM
- Composer

#### Getting Started

To install the development dependencies, run:

```shell
composer install
npm install
```

To rebuild the assets, run:

```shell
npx webpack
```

(Requires [npx](https://www.npmjs.com/package/npx))

#### Enable REST API

Included are endpoints to obtain the schedule's sessions and people these endpoints are
disabled by default. To enable one or both of these set an environmental variable `SCHEDULE_BUILDER_API`
with a comma or space delimited value. 

```text
# Either comment out or remove SCHEDULE_BUILDER_API to disable.
# Use a common or space delimited value to indicate which API
# endpoints to enable.
#
# This really should belong in the plugin's settings UI. Todo for another day.
#
# +-----------+-------------------------------------------------------------------------+
# | post_type | endpoints                                                               |
# +-----------|-------------------------------------------------------------------------|
# | people    | schedule-builder/people                                                 |
# |           | schedule-builder/people/(?P<post_id>[\d+])                              |
# +-----------+-------------------------------------------------------------------------+
# |           | /schedule-builder/sessions                                              |
# | sessions  | /schedule-builder/sessions/(?P<session_id>[a-zA-Z0-9]+-[a-zA-Z0-9]+)    |
# |           | /schedule-builder/sessions/(?P<post_id>[\d]+)                           |
# +-----------+-------------------------------------------------------------------------+
#SCHEDULE_BUILDER_API=people               # people endpoints only
#SCHEDULE_BUILDER_API=sessions             # sessions endpoints only
#SCHEDULE_BUILDER_API="people sessions"    # both people and sessions
```

