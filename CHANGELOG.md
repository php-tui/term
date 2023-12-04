CHANGELOG
=========

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

