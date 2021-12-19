<?php

namespace Makhnanov\Php81SelfFilling\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

class ExcessException extends Exception
{
    #[Pure]
    public function __construct(private array $excess)
    {
        parent::__construct(
            'Data '
            . join(', ', array_keys($excess))
            . (count($excess) === 1 ? ' is ' : ' are ')
            . 'excess.'
        );
    }

    public function getExcess(): array
    {
        return $this->excess;
    }
}
