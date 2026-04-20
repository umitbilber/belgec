<?php

declare(strict_types=1);

namespace App\Interfaces;

interface EdmGorulduServiceInterface
{
    public function gorulduler(): array;
    public function gorulduMu(string $uuid): bool;
    public function gorulduIsaretle(string $uuid): void;
    public function topluGorulduIsaretle(array $uuidler): void;
}