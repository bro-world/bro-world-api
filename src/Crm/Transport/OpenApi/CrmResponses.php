<?php

declare(strict_types=1);

namespace App\Crm\Transport\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Response(response: 'ValidationFailed422', description: 'Validation failed.', content: new OA\JsonContent(ref: '#/components/schemas/CrmErrorResponse'))]
#[OA\Response(response: 'NotFound404', description: 'Resource not found.', content: new OA\JsonContent(ref: '#/components/schemas/CrmErrorResponse'))]
#[OA\Response(response: 'Unauthorized401', description: 'Authentication required.', content: new OA\JsonContent(ref: '#/components/schemas/CrmErrorResponse'))]
#[OA\Response(response: 'Forbidden403', description: 'Access denied.', content: new OA\JsonContent(ref: '#/components/schemas/CrmErrorResponse'))]
final class CrmResponses
{
}
