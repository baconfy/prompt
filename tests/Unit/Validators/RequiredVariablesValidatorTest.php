<?php

declare(strict_types=1);

use Baconfy\Prompt\Exceptions\MissingRequiredVariablesException;
use Baconfy\Prompt\Validators\RequiredVariablesValidator;

it('passes when no required variables are declared', function (): void {
    $validator = new RequiredVariablesValidator;

    expect(fn () => $validator->validate(['model' => 'claude'], []))
        ->not->toThrow(MissingRequiredVariablesException::class);
});

it('passes when all required variables are provided', function (): void {
    $validator = new RequiredVariablesValidator;

    expect(fn () => $validator->validate(
        ['required' => ['name', 'context']],
        ['name' => 'John', 'context' => 'support'],
    ))->not->toThrow(MissingRequiredVariablesException::class);
});

it('throws when a required variable is missing', function (): void {
    $validator = new RequiredVariablesValidator;

    $validator->validate(
        ['required' => ['name', 'context']],
        ['name' => 'John'],
    );
})->throws(MissingRequiredVariablesException::class, 'context');

it('exposes the missing variables on the exception', function (): void {
    $validator = new RequiredVariablesValidator;

    try {
        $validator->validate(['required' => ['name', 'context', 'lang']], ['name' => 'John']);
        expect(true)->toBeFalse();
    } catch (MissingRequiredVariablesException $e) {
        expect($e->variables)->toBe(['context', 'lang']);
    }
});

it('skips validation when required is not an array', function (): void {
    $validator = new RequiredVariablesValidator;

    expect(fn () => $validator->validate(['required' => 'not-an-array'], []))->not->toThrow(MissingRequiredVariablesException::class);
});

it('skips non-string entries in the required list', function (): void {
    $validator = new RequiredVariablesValidator;

    expect(fn () => $validator->validate(
        ['required' => [123, 'name']],
        ['name' => 'John'],
    ))->not->toThrow(MissingRequiredVariablesException::class);
});
