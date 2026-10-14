<?php

use App\Support\BdPhone;

it('normalises valid Bangladeshi mobile numbers', function (string $input, string $expected) {
    expect(BdPhone::normalize($input))->toBe($expected);
})->with([
    ['01712345678', '01712345678'],
    ['+8801812345678', '01812345678'],
    ['8801912345678', '01912345678'],
    ['017-1234-5678', '01712345678'],
    ['01312345678', '01312345678'],
]);

it('rejects invalid Bangladeshi mobile numbers', function (string $input) {
    expect(BdPhone::normalize($input))->toBeNull();
})->with([
    ['01212345678'],
    ['0171234567'],
    ['017123456789'],
    ['+8811712345678'],
    [''],
]);
