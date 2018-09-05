# ScheduleBuilder

> ScheduleBuilder is a WordPress plugin for building interactive agendas for scientific meetings.

[![Latest Stable Version](https://poser.pugx.org/asmbs/wp-schedule-builder/v/stable)](https://packagist.org/packages/asmbs/wp-schedule-builder)[![Latest Unstable Version](https://poser.pugx.org/asmbs/wp-schedule-builder/v/unstable)](https://packagist.org/packages/asmbs/wp-schedule-builder)



## Requirements

- PHP 7.0+
- A Composer-based WordPress stack like [Bedrock](https://github.com/roots/bedrock)
- [Node + NPM](https://nodejs.org)
- The [Advanced Custom Fields (ACF) Pro](https://www.advancedcustomfields.com/pro/) WordPress plugin



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

```
composer install
npm install
```

To rebuild the assets, run:

```
npx webpack
```

(Requires [npx](https://www.npmjs.com/package/npx))

