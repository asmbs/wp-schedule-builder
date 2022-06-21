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

## RESTful API

As of v5.0 this plugin is bundled with a **GET** only api. To enable this feature set the `SCHEDULE_BUILDER_API` 
environmental variable `1`. For details on the endpoints provided please see the 
[Schedule-Builder API documentation](docs/index.html) 

## Webhook

With the RESTful API enable it is possible to report changes to session, abstract, and person post types by setting
`SCHEDULE_BUILDER_WEBHOOK_URL` environmental variable to an absolute URL. The webhook MUST accept HTTP POST method with 
a JSON body,

```json
{
  "@type": "{post_type}",
  "@id": "{post_type}/{post_id}",
  "import_id": "{post_type}_{post_id}",
  "update": true|false,
  "status": publish|trash
}
```

> Where `{post_type}` is either `session|abstract|person`
> and `{post_id}` is the Wordpress post id
 
When sending the POST request to the webhook, if the environmental variable `SCHEDULE_BUILDER_WEBHOOK_AUTHORIZATION` is
configured its value is added as a bearer token in the request Authorization header. 

### Example

The application `.env` file as: 

```text
# .env
SCHEDULE_BUILDER_WEBHOOK_AUTHORIZATION=the_webhooks_authorization_token_value
```
will translate to an HTTP authorization header:

```text
Authorization: Bearer the_webhooks_authorization_token_value
```

## Viewing API Documentation

Once deployed to view the API documents navigate to `https://<meeting base url>/app/plugins/wp-schedule-builder/docs/` 
where `<meetting base url>` is the meetings Wordpress site's FQDN. Information on compiling the API documentation see 
[docs/open-api.md](./docs/open-api.md)
