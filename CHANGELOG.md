# Changelog
## 2.0.0 (current)
- Add people entity, replacing speakers and authors
- Add support for person photo (download to WP media library)

___ 



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
