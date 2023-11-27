<?php

use PhpTui\Term\Actions;

// script to generate the README content for the Actions:: class.

require __DIR__ . '/../vendor/autoload.php';

$docs = [];
$docs[] = '| method | description |';
$docs[] = '| --- | --- |';
$reflection = new ReflectionClass(Actions::class);
foreach ($reflection->getMethods() as $reflectionMethod) {
    $prose = parseProse($reflectionMethod->getDocComment());
    $docs[] = sprintf('| `Actions::%s` | %s |', $reflectionMethod->getName(), str_replace("\n", "<br/>", $prose));
}

echo implode("\n", $docs);

function parseProse(string $docblock): string
{
    $chars = mb_str_split($docblock);
    $text = [];
    $mode = 'whitespace';

    foreach ($chars as $char) {
        if ($char === "\n") {
            $mode = 'whitespace';
            $text[] = "\n";
            continue;
        }
        if ($mode === 'whitespace') {
            if ($char === ' ') {
                continue;
            }
            if ($char === '/' || $char === '*') {
                $mode = 'prefix';
                continue;
            }
        } elseif ($mode === 'prefix') {
            if ($char === ' ') {
                $mode = 'text';
                continue;
            }
        } elseif ($mode === 'text') {
            $text[] = $char;
            continue;
        }
    }

    return trim(implode('', $text));
}
