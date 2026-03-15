<?php

namespace App\Services\Exceptions;

/**
 * Dedicated exception for 401/403 responses from remote services.
 *
 * Carries the HTTP status plus optional error_code, correlation_id,
 * and the originating service name so callers can make user-facing
 * decisions (e.g. logout + redirect) and operators can diagnose
 * which service rejected the token.
 */
class UnauthorizedRemoteException extends ExternalServiceException
{
    public function __construct(
        string $message = 'Unauthorized',
        int $statusCode = 401,
        ?string $errorCode = null,
        ?string $correlationId = null,
        array $context = [],
        public readonly ?string $serviceName = null,
    ) {
        parent::__construct($message, $statusCode, $context, $errorCode, $correlationId);
    }
}
