<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Interfaces\ApiRequestInterface;
use Exception;
use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractApiRequest extends FormRequest implements ApiRequestInterface
{
    /**
     * @return array|null
     * @throws Exception
     */
    public function validationData(): ?array
    {
        if (!$this->ajax()) {
            //            throw new Exception('Is not ajax.');
        }

        return array_merge(
            $this->all(),
            $this->json()->all(),
            $this->route()->parameters()
        );
    }

    public function toData(): mixed
    {
        if (!method_exists($this, 'getDtoClass')) {
            throw new Exception('Method getDtoClass() must be implemented in child class');
        }

        $dtoClass = $this->getDtoClass();

        if (!class_exists($dtoClass)) {
            throw new Exception("DTO class {$dtoClass} does not exist");
        }

        return $dtoClass::from($this->validated());
    }

}
