<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Counterparty;

use App\DTO\Counterparty\CreateCounterpartyDto;
use App\Http\Api\V1\AbstractController;
use App\Http\Requests\Counterparty\CreateCounterpartyRequest;
use App\Http\Resources\CounterpartyResource;
use App\Services\CounterpartyService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Counterparties",
 *     description="API for managing counterparties (контрагенты)"
 * )
 */
class CounterpartyCreateController extends AbstractController
{
    public function __construct(private CounterpartyService $service)
    {
    }

    /**
     * @OA\Post(
     *      path="/api/v1/counterparties",
     *      summary="Создать контрагента по ИНН",
     *      tags={"Counterparties"},
     *      security={{"Bearer": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CreateCounterpartyRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Контрагент успешно создан",
     *          @OA\JsonContent(ref="#/components/schemas/CounterpartyResource")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Ошибка валидации",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Неавторизован",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function __invoke(CreateCounterpartyRequest $request): JsonResponse
    {
        $dto = CreateCounterpartyDto::fromRequest($request);
        $counterparty = $this->service->createFromInn($request->user(), $dto);

        return response()->json(new CounterpartyResource($counterparty), 201);
    }
}
