# Changelog  

Starting with v3.0, the format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

Starting with v3.0, this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).



## [Unreleased]



## [4.0.1] - 2019-05-30

### Fixed

- A bug with loading room selections when the saved database value had a prepended space.



## [4.0.0] - 2019-05-28

### Changed

- **[BC Break]** Changed the JavaScript to support ACF's new JavaScript API introduced as a breaking change in ACF v5.7.
- Formatted all PHP files into WordPress style.
- Formatted all JavaScript files into Airbnb style.
- Updated all dependencies.

### Fixed

- Expanded the reserved slug word bugfix detailed in v3.0.0 to include plural versions.



## [3.0.1] - 2018-10-09

### Security

- Fixed a security vulnerability with an NPM dependency.



## [3.0.0] - 2018-09-05

### Added

- Added an input mask for client-side validation of start and end times .
- Added server-side validation of start and end times.

### Changed

- **[BC Break]** Dropped support for PHP <7.0.
- **[BC Break]** Switched from Gulp and Bower to Webpack.
- Rewrote all JavaScript to ES6.
- Began including built files instead of forcing dependent software to build on deploy.
- Replaced deprecated `ddeboer/data-import` with `portphp/steps`.

### Fixed

- Fixed a bug where admins could use a reserved slug word without an error.
- Cleaned up code.



### 2.2.5 - August 2, 2018

- Revert ddeboer/data-import to v0.18.0

### 2.2.5 - July 11, 2018

- Fix bug with venues with apostrophes in their names

### 2.2.4

- Update outdated dependencies

### 2.2.3

- Fix issue with legacy Bower versions

### 2.2.2

- Fix deployment bug with Node 10

### 2.2.1

- Fix typo in session import preventing keyword field

### 2.2.0
- Add keyword taxonomy support to session post type
- Add support for keyword taxonomy to session importer  

### 2.1.4
- Add support for ACF v5.6

### 2.1.3
- Add model support for multiple speakers

### 2.1.2
- Add support for multiple speakers in session item

### 2.1.1
- Add sort order to abstract authors when imported

### 2.1.0
- Add permalink support to research abstracts
- Add title support to abstracts and people
- Add `evaluable` custom field

### 2.0.0
- Add people entity, replacing speakers and authors
- Add support for person photo (download to WP media library)

### 1.4.4
- Remove limit on author relationships when importing and replacing abstracts

### 1.4.3
- Remove limit on author relationships when importing abstracts

### 1.4.2
- Set the data-import version to squash bugs

### 1.4.1
#### Bugfixes
- Added a call to `wp_cache_flush()` to the import workflow to dump the cache after every record write. This solves an issue with ACF loading a stale field value from the cache instead of reading from the database.

### 1.4.0
#### Updates
- Separated session taxonomy filters in the schedule grid builder to allow more complex query logic—specifically, ANDing societies and ORing other terms.

### 1.3.6
#### Bugfixes
- Fixed a few places where missing or invalid session date/time data slipped through the cracks and caused crashes.

### 1.3.5
#### Bugfixes
- Fixed a field reference error causing sessions with _no_ progress markers selected to be marked as complete.

### 1.3.4

Literally, three minutes. Come on.

#### Bugfixes
- Fixed an error in the start/end time query modifications from 1.3.3.

#### Updates
- Added composer version badges to readme, because why not.

### 1.3.3
#### Bugfixes
- Fixed a [strict-standards error](https://github.com/asmbs/wp-schedule-builder/issues/1) in variable passing.
- Modified the day start/end time calculation queries to [omit blank and unpublished results](https://github.com/asmbs/wp-schedule-builder/issues/2).

### 1.3.2
#### Bugfixes
- Added checks to handle blank dates and times in sessions and their agendas.

### 1.3.1
#### Bugfixes
- The import writers' `addMeta` and `addTerm` methods now ignore any empty values.

#### Updates
- When errors occur during an import, up to 10 of those errors will be listed on the import results page.

### 1.3.0
#### Updates
- Added the ability to round day start/end times to the appropriate nearest hour (_floor_ for start time, _ceiling_ for end time).
- Updated the default string format for day start/end methods to return the time instead of the date.

### 1.2.0
#### Updates
- Added counting methods to both the full-schedule listing and per-day listings.

### 1.1.0
#### Updates
- Added builder classes for generating a full or filtered event schedule.

### 1.0.1
- Fixed slug references within `*PostType` classes to use `static` instead of `self`

### 1.0.0
- Initial release
