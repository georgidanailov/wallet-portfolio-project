<?php

namespace App\DTO;

class PaginationDTO
{
    public function __construct(
        private array $items,
        private int $currentPage,
        private int $itemsPerPage,
        private int $totalItems
    ) {}

    public function getItems(): array
    {
        return $this->items;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->totalItems / $this->itemsPerPage);
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function getNextPage(): int
    {
        return min($this->getTotalPages(), $this->currentPage + 1);
    }

    public function getPageNumbers(): array
    {
        $totalPages = $this->getTotalPages();
        $currentPage = $this->getCurrentPage();

        // Show max 5-page numbers around current page
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        return range($start, $end);
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'currentPage' => $this->currentPage,
            'itemsPerPage' => $this->itemsPerPage,
            'totalItems' => $this->totalItems,
            'totalPages' => $this->getTotalPages(),
            'hasPreviousPage' => $this->hasPreviousPage(),
            'hasNextPage' => $this->hasNextPage(),
            'previousPage' => $this->getPreviousPage(),
            'nextPage' => $this->getNextPage(),
            'pageNumbers' => $this->getPageNumbers()
        ];
    }
}
