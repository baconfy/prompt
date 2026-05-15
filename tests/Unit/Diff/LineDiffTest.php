<?php

declare(strict_types=1);

use Baconfy\Prompt\Diff\LineDiff;

beforeEach(function (): void {
    $this->differ = new LineDiff;
});

it('marks every line as equal when from and to are identical', function (): void {
    $result = $this->differ->diff("a\nb\nc", "a\nb\nc");

    expect($result)->toBe([
        ['type' => 'equal', 'line' => 'a'],
        ['type' => 'equal', 'line' => 'b'],
        ['type' => 'equal', 'line' => 'c'],
    ]);
});

it('marks all lines as added when from is empty', function (): void {
    $result = $this->differ->diff('', "a\nb");

    expect($result)->toBe([
        ['type' => 'added', 'line' => 'a'],
        ['type' => 'added', 'line' => 'b'],
    ]);
});

it('marks all lines as removed when to is empty', function (): void {
    $result = $this->differ->diff("a\nb", '');

    expect($result)->toBe([
        ['type' => 'removed', 'line' => 'a'],
        ['type' => 'removed', 'line' => 'b'],
    ]);
});

it('detects a line replacement as one removed + one added', function (): void {
    $result = $this->differ->diff("a\nb\nc", "a\nX\nc");

    expect($result)->toBe([
        ['type' => 'equal', 'line' => 'a'],
        ['type' => 'removed', 'line' => 'b'],
        ['type' => 'added', 'line' => 'X'],
        ['type' => 'equal', 'line' => 'c'],
    ]);
});

it('detects a pure insertion in the middle', function (): void {
    $result = $this->differ->diff("a\nc", "a\nb\nc");

    expect($result)->toBe([
        ['type' => 'equal', 'line' => 'a'],
        ['type' => 'added', 'line' => 'b'],
        ['type' => 'equal', 'line' => 'c'],
    ]);
});

it('detects a pure deletion in the middle', function (): void {
    $result = $this->differ->diff("a\nb\nc", "a\nc");

    expect($result)->toBe([
        ['type' => 'equal', 'line' => 'a'],
        ['type' => 'removed', 'line' => 'b'],
        ['type' => 'equal', 'line' => 'c'],
    ]);
});

it('returns an empty array when both inputs are empty', function (): void {
    expect($this->differ->diff('', ''))->toBe([]);
});
