<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Domain;

final class Student
{
    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('Student id cannot be empty.');
        }
    }
}
