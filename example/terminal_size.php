<?php

use PhpTui\Term\Size;
use PhpTui\Term\Terminal;

require __DIR__ . '/../vendor/autoload.php';

$terminal = Terminal::new();

$size = $terminal->info(Size::class);
if ($size) {
    echo $size->__toString() . "\n";
} else {

    echo 'Could not determine terminal size'."\n";
}
