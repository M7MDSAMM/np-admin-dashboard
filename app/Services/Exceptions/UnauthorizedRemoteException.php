<?php

namespace App\Services\Exceptions;

/**
 * Dedicated exception for 401/403 responses from the User Service.
 *
 * Carries the HTTP status plus optional error_code and correlation_id
 * so callers can make user-facing decisions (e.g. logout + redirect).
 */
class UnauthorizedRemoteException extends ExternalServiceException
{
    public function __construct(
        string $message = 'Unauthorized',
        int $statusCode = 401,
        public readonly ?string $errorCode = null,
        public readonly ?string $correlationId = null,
        array $context = [],
    ) {
        parent::__construct($message, $statusCode, $context);
    }
}
