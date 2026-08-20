<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Exceptions;

use AzureOss\Storage\Blob\Models\BlobErrorCode;
use AzureOss\Storage\Common\Exceptions\RequestExceptionDeserializer;
use AzureOss\Storage\Common\Exceptions\StorageErrorResponse;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class BlobStorageExceptionDeserializer implements RequestExceptionDeserializer
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

        return new BlobStorageException(
            $error->message,
            previous: $e,
            errorCode: BlobErrorCode::tryFrom($error->code),
            errorCodeValue: $error->code,
            requestId: $error->requestId,
            statusCode: $error->statusCode,
        );
    }
}
