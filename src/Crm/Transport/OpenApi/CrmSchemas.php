<?php

declare(strict_types=1);

namespace App\Crm\Transport\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CrmErrorResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Validation failed.'),
        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'object')),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PaginatedResponse',
    required: ['items'],
    properties: [
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'pagination', type: 'object', nullable: true),
        new OA\Property(property: 'meta', type: 'object', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmContact',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'firstName', type: 'string'),
        new OA\Property(property: 'lastName', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmEmployee',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'fullName', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmProject',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'status', type: 'string'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmTask',
    required: ['title', 'projectId'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['todo', 'in_progress', 'blocked', 'done'], nullable: true),
        new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high', 'critical'], nullable: true),
        new OA\Property(property: 'dueAt', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'estimatedHours', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'projectId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'sprintId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'assigneeIds', type: 'array', items: new OA\Items(type: 'string', format: 'uuid'), nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmTaskRequest',
    required: ['title', 'taskId', 'repositoryId'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected'], nullable: true),
        new OA\Property(property: 'resolvedAt', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'taskId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'repositoryId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'assigneeIds', type: 'array', items: new OA\Items(type: 'string', format: 'uuid'), nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmGithubRepository',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'fullName', type: 'string'),
        new OA\Property(property: 'private', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmGithubIssue',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'number', type: 'integer'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'state', type: 'string'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CrmGithubBranch',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'sha', type: 'string', nullable: true),
        new OA\Property(property: 'url', type: 'string', nullable: true),
    ],
    type: 'object'
)]
final class CrmSchemas
{
}
