<?php
declare(strict_types=1);
namespace App\Interfaces;

interface ApiRequestInterface
{
    public function authorize(): bool;
}
