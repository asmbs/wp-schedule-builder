# Changelog

## 1.3.3 (current)
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
