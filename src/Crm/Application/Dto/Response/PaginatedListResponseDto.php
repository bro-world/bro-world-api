<?php

declare(strict_types=1);

namespace App\Crm\Application\Dto\Response;

final readonly class PaginatedListResponseDto
{
    /** @param list<array<string,mixed>> $items */
    public function __construct(
        public array $items,
        public ?int $total = null,
        public ?int $page = null,
        public ?int $limit = null,
    ) {
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        $response = ['items' => $this->items];
        if ($this->total !== null) {
            $response['total'] = $this->total;
        }
        if ($this->page !== null) {
            $response['page'] = $this->page;
        }
        if ($this->limit !== null) {
            $response['limit'] = $this->limit;
        }

        return $response;
    }
}
