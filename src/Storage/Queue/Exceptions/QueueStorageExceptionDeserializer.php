<?php

declare(strict_types=1);

namespace AzureOss\Storage\Queue\Exceptions;

use AzureOss\Storage\Common\Exceptions\RequestExceptionDeserializer;
use AzureOss\Storage\Common\Exceptions\StorageErrorResponse;
use AzureOss\Storage\Queue\Models\QueueErrorCode;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class QueueStorageExceptionDeserializer implements RequestExceptionDeserializer
{
    public function deserialize(RequestException $e): \Exception
    {
        $getResponse = [$e, 'getResponse'];
        if (! is_callable($getResponse)) {
            return $e;
        }

        $response = $getResponse();
        if (! $response instanceof ResponseInterface) {
            return $e;
        }

        $error = StorageErrorResponse::fromResponse($response);
        if ($error === null) {
            return $e;
        }

        return new QueueStorageException(
            $error->message,
            previous: $e,
            errorCode: QueueErrorCode::tryFrom($error->code),
            errorCodeValue: $error->code,
            requestId: $error->requestId,
            statusCode: $error->statusCode,
        );
    }
}
