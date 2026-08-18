<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests exercise controllers directly. Keep the session,
        // authentication, validation and other web middleware active, but
        // disable only CSRF validation so tests do not need browser-issued
        // tokens on every POST/PUT request.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
