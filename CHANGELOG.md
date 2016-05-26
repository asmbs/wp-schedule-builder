# Changelog

## 1.3.1 (current)
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
