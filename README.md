PHP Term
========

[![CI](https://github.com/php-tui/term/actions/workflows/ci.yml/badge.svg)](https://github.com/php-tui/term/actions/workflows/ci.yml)

Low-level terminal control library **heavily** inspired by
[crossterm](https://github.com/crossterm-rs/crossterm).

- Command pattern used to allow buffering of updates.
- ANSI _parser_ included to translate output back to commands.
- Cursor:
  - [x] Hide or show the cursor
  - [x] Retrieve and change the cursor position
  - [x] Cursor up / down / start of line etc
  - [x] Store and restore the cursor position
  - [x] Enable/disable cursor blinking
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
  - [x] Scroll up / down
  - [x] Set terminal title
  - [x] Enable/disable line wrapping
- Events:
  - [x] Input events (e.g. keyboard events)
  - [x] Mouse (position, press, release, button, drag)
  - [x] Terminal resize events
  - [x] Modifiers (shift, alt, ctrl) support for mouse and keys

Installation
------------

```
$ composer require phptui/term
```

Usage
-----

### Actions

You can send data to the terminal using _actions_.

```php
<?php

$terminal = Terminal::new();

// queue an action
$terminal->queue(Actions::printString('Hello World'));

// flush the queue to the terminal
$terminal->flush();

// or you can execute it directly
$terminal->execute(Actions::printString('Hello World'));
```

All actions are made available via. the `Actions` factory:

| method | description |
| --- | --- |
| `Actions::requestCursorPosition` | Request the cursor position.<br/><br/>This will (hopefully) be returned by the terminal and will be provided<br/>as an `PhpTui\Term\Event\CursorPositionEvent`. |
| `Actions::alternateScreenEnable` | Enable the alternate screen.<br/><br/>Allows switching back to the users previous "screen" later. |
| `Actions::alternateScreenDisable` | Disable the alternate screen |
| `Actions::printString` | Echo a standard string to the terminal |
| `Actions::cursorShow` | Show the cursor |
| `Actions::cursorHide` | Hide the cursor |
| `Actions::setRgbForegroundColor` | Set the foreground color using RGB |
| `Actions::setRgbBackgroundColor` | Set the background color using RGB |
| `Actions::setForegroundColor` | Set the foreground color to one of the ANSI base colors |
| `Actions::setBackgroundColor` | Set the background color to one of the ANSI base colors |
| `Actions::moveCursor` | Move the cursor to an absolute position.<br/><br/>The top left cell is 0,0. |
| `Actions::reset` | Reset all modes (styles and colors) |
| `Actions::bold` | Enable or disable the bold styling |
| `Actions::dim` | Enable or disable the dim styling |
| `Actions::italic` | Enable or disable the italic styling |
| `Actions::underline` | Enable or disable the underline styling |
| `Actions::slowBlink` | Enable or disable the slow blink styling |
| `Actions::rapidBlink` | Enable or disable the rapid blink styling |
| `Actions::reverse` | Enable or disable the reverse blink styling |
| `Actions::hidden` | Enable or disable the hidden styling - useful for passwords. |
| `Actions::strike` | Enable or disable the strike-through styling |
| `Actions::clear` | Perform a clear operation.<br/><br/>The type of clear operation is given with the Enum for example<br/><br/>`Actions::clear(ClearType::All)`<br/><br/>Will clear the entire screen. |
| `Actions::enableMouseCapture` | Enable mouse capture.<br/><br/>Once this action has been issued mouse events will be made available. |
| `Actions::disableMouseCapture` | Disable mouse capture |
| `Actions::scrollUp` | Scroll the terminal up the given number of rows |
| `Actions::scrollDown` | Scroll the terminal down the given number of rows |
| `Actions::setTitle` | Set the title of the terminal for the current process. |
| `Actions::lineWrap` | Enable or disable line wrap |
| `Actions::moveCursorNextLine` | Move the cursor down and to the start of the next line (or the given number of lines) |
| `Actions::moveCursorPreviousLine` | Move the cursor up and to the start of the previous line (or the given number of lines) |
| `Actions::moveCursorToColumn` | Move the cursor to the given column (0 based) |
| `Actions::moveCursorToRow` | Move the cursor to the given row (0 based) |
| `Actions::moveCursorUp` | Move cursor up 1 or the given number of rows. |
| `Actions::moveCursorRight` | Move cursor right 1 or the given number of columns. |
| `Actions::moveCursorDown` | Move cursor down 1 or the given number of rows. |
| `Actions::moveCursorLeft` | Move cursor left 1 or the given number of columns. |
| `Actions::saveCursorPosition` | Save the cursor position |
| `Actions::restoreCursorPosition` | Restore the cursor position |
| `Actions::enableCusorBlinking` | Enable cursor blinking |
| `Actions::disableCursorBlinking` | Disable cursor blinking |
| `Actions::setCursorStyle` | Set the cursor style |
