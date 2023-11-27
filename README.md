PHP Term
========

[![CI](https://github.com/php-tui/term/actions/workflows/ci.yml/badge.svg)](https://github.com/php-tui/term/actions/workflows/ci.yml)

Low-level terminal control library **heavily** inspired by
[crossterm](https://github.com/crossterm-rs/crossterm).

- Cursor:
  - [x] Hide or show the cursor
  - [x] Retrieve and change the cursor position
  - [x] Store and restore the cursor position
  - [ ] Enable/disable cursor blinking
- Styling:
  - [x] Standard 16 ANSI colors
  - [x] RGB color support
  - [x] 256 color support
  - [x] Bold, italic, underscore, strike, etc.
- Terminal:
  - [x] Various methods to clear it
  - [x] Set/get the terminal size
  - [x] Alternate screen
  - [x] Raw screen
  - [ ] Scroll up / down
  - [ ] Set terminal title
  - [ ] Enable/disable line wrapping
- Events:
  - [x] Input events (e.g. keyboard events)
  - [x] Mouse (position, press, release, button, drag)
  - [ ] Terminal resize events
  - [x] Modifiers (shift, alt, ctrl) support for mouse and keys


Installation
------------

```
$ composer require phptui/term
```

Usage
-----
