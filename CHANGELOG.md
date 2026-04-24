CHANGELOG
=========

## Unreleased

- CI: bump `actions/checkout` v2 → v5 and `ramsey/composer-install` v1 → v3
- CI: test against PHP 8.3, 8.4 and 8.5
- CI: run PHPUnit on Linux, macOS and Windows
- Fix PHP 8.5 deprecation in `EventParser::charToEvent()`: passing a multi-byte UTF-8 string to `ord()` is deprecated. Behavior unchanged on previous PHP versions.

## 0.3.4

- Bug fix: disable mouse capture uses wrong ansi codes #9

## 0.3.3

- Bug fix: use `stream_get_contents` and check for empty string #199

## 0.3.2

- Bug fix: fix alternate output style from STTY #185

## 0.3.1

- Bug fix: fix Stty size handler when no match

## 0.3.0

- Moved `Size` class to it's own namespace and added int types

## 0.2.0

- Support variadics on queue() and execute() #14
- Add bounded int types to API
- Removed the HTMLCanvas painter #2
- Renamed LoadedEventProvider => ArrayEventProvider
- Renamed InMemoryReder => ArrayReader
- Renamed BufferWriter => StringWriter
- Renamed BufferPainter => ArrayPainter

