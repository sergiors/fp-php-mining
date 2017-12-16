<?php

use function Prelude\{pipe, filter, map};

require __DIR__ . '/vendor/autoload.php';

$get = function (string $fname) {
    return function () use ($fname) {
        return preg_split('/\n/', trim(file_get_contents($fname)));
    };
};

$put = function (string $fname) {
    return function (array $ls) use ($fname) {
        return file_put_contents($fname, join("\n", $ls));
    };
};

$mapIds = map(function (string $str) {
    return (int) explode(' ', $str)[1];
});

$physicalL = $get('classificao-saude.txt');
$classifiedL = $get('classificao-notas.txt');

$physicalL2 = pipe(
    $physicalL,
    filter(function (string $str) {
        return 'APTO' === array_slice(explode(' ', trim($str)), -1)[0];
    }),
    $mapIds
)(null);

$classifiedL2 = pipe(
    $classifiedL,
    filter(function (string $str) use ($physicalL2) {
        $str = trim($str);
        preg_match('/[0-9]{5}/', $str, $matches);
        return in_array($matches[0], $physicalL2);
    })
);

pipe($classifiedL2, $put('classificado.txt'))(null);
