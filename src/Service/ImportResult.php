<?php

namespace App\Service;

use App\Dto\CardDto;

final class ImportResult {
    
    /**
    * @param CardDto[] $cards
    */
    public function __construct(
        public array $cards,
        public ?string $errorMessage = null
    ) {}

    public function cardCount(): int 
    {
        return count($this->cards);
    }

    public function hasError(): bool
    {
        return $this->errorMessage !== null;
    }
}