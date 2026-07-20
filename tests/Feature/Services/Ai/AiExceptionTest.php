<?php

use App\Services\Ai\Exceptions\AiException;

it('does not define a context() method, which Laravel exception reporting reserves for array-returning context', function () {
    $exception = new AiException('boom', 500, 'truncated body');

    // Illuminate\Foundation\Exceptions\Handler::exceptionContext() calls
    // ->context() on any Throwable that defines it and array_merge()s the
    // result -- a non-array return type crashes exception reporting with
    // a TypeError the moment an AiException is left uncaught. This test
    // guards against reintroducing a method with that exact name.
    expect(method_exists($exception, 'context'))->toBeFalse();
});

it('exposes status code and truncated body via non-colliding accessors', function () {
    $exception = new AiException('boom', 502, 'some truncated response body');

    expect($exception->statusCode())->toBe(502)
        ->and($exception->truncatedBody())->toBe('some truncated response body')
        ->and($exception->getMessage())->toBe('boom');
});

it('allows a null truncated body', function () {
    $exception = new AiException('boom', 500);

    expect($exception->truncatedBody())->toBeNull();
});
