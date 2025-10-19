<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicatePackageException extends RuntimeException
{
    /**
     * @param array<int, string> $duplicateIds
     */
    public function __construct(
        private readonly array $duplicateIds,
        string $message = 'Duplicate packages detected.'
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<int, string>
     */
    public function getDuplicateIds(): array
    {
        return $this->duplicateIds;
    }
}
