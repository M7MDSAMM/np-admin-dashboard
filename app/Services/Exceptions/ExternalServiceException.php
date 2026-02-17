<?php

namespace App\Services\Exceptions;

use RuntimeException;

/**
 * Thrown when an outbound HTTP call to another microservice fails.
 *
 * Carries the HTTP status code and an optional context array (e.g.
 * validation errors returned by the remote service) so that controllers
 * can display meaningful feedback to the user.
 *
 * Example usage in a controller:
 *   catch (ExternalServiceException $e) {
 *       return back()->with('error', $e->getMessage())
 *                    ->withErrors($e->context);  // validation errors
 *   }
 */
class ExternalServiceException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 502,
        public readonly array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
