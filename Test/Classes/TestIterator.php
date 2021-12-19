<?php

namespace Makhnanov\Php81SelfFilling\Test\Classes;

use Iterator;

class TestIterator implements Iterator
{
    private array $data = [
        'firstElement',
        'secondElement',
        'lastElement',
    ];

    public function __construct(private int $position = 0)
    {
    }

    public function rewind(): void
    {
        var_dump(__METHOD__);
        $this->position = 0;
    }

    public function current(): string
    {
        return $this->data[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        var_dump(__METHOD__);
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }
}
