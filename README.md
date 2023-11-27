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

```
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
| requestCursorPosition | Request the cursor position.<br/><br/>This will (hopefully) be returned by the terminal and will be provided<br/>as an `PhpTui\Term\Event\CursorPositionEvent`. |
| alternateScreenEnable | Enable the alternate screen.<br/><br/>Allows switching back to the users previous "screen" later. |
| alternateScreenDisable | Disable the alternate screen |
| printString | Echo a standard string to the terminal |
| cursorShow | Show the cursor |
| cursorHide | Hide the cursor |
| setRgbForegroundColor | Set the foreground color using RGB |
| setRgbBackgroundColor | Set the background color using RGB |
| setForegroundColor | Set the foreground color to one of the ANSI base colors |
| setBackgroundColor | Set the background color to one of the ANSI base colors |
| moveCursor | Move the cursor to an absolute position.<br/><br/>The top left cell is 0,0. |
| reset | Reset all modes (styles and colors) |
| bold | Enable or disable the bold styling |
| dim | Enable or disable the dim styling |
| italic | Enable or disable the italic styling |
| underline | Enable or disable the underline styling |
| slowBlink | Enable or disable the slow blink styling |
| rapidBlink | Enable or disable the rapid blink styling |
| reverse | Enable or disable the reverse blink styling |
| hidden | Enable or disable the hidden styling - useful for passwords. |
| strike | Enable or disable the strike-through styling |
| clear | Perform a clear operation.<br/><br/>The type of clear operation is given with the Enum for example<br/><br/>```<br/>Actions::clear(ClearType::All)<br/>```<br/><br/>Will clear the entire screen. |
| enableMouseCapture | Enable mouse capture.<br/><br/>Once this action has been issued mouse events will be made available. |
| disableMouseCapture | Disable mouse capture |
| scrollUp | Scroll the terminal up the given number of rows |
| scrollDown | Scroll the terminal down the given number of rows |
| setTitle | Set the title of the terminal for the current process. |
| lineWrap | Enable or disable line wrap |
| moveCursorNextLine | Move the cursor down and to the start of the next line (or the given number of lines) |
| moveCursorPreviousLine | Move the cursor up and to the start of the previous line (or the given number of lines) |
| moveCursorToColumn | Move the cursor to the given column (0 based) |
| moveCursorToRow | Move the cursor to the given row (0 based) |
| moveCursorUp | Move cursor up 1 or the given number of rows. |
| moveCursorRight | Move cursor right 1 or the given number of columns. |
| moveCursorDown | Move cursor down 1 or the given number of rows. |
| moveCursorLeft | Move cursor left 1 or the given number of columns. |
| saveCursorPosition | Save the cursor position |
| restoreCursorPosition | Restore the cursor position |
| enableCusorBlinking | Enable cursor blinking |
| disableCursorBlinking | Disable cursor blinking |
| setCursorStyle | Set the cursor style |
