<?php

namespace App\Http\Api\V1\Counterparty;

use App\Http\Api\V1\AbstractController;
use App\Http\Resources\CounterpartyResource;
use App\Services\CounterpartyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounterpartyListController extends AbstractController
{
    public function __construct(private CounterpartyService $service)
    {
    }

    /**
     * @OA\Get(
     *      path="/api/v1/counterparties",
     *      summary="Получить список контрагентов текущего пользователя",
     *      tags={"Counterparties"},
     *      security={{"Bearer": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Номер страницы",
     *          @OA\Schema(type="integer", default=1)
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Количество элементов на страницу",
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Список контрагентов",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/CounterpartyResource")
     *              ),
     *              @OA\Property(
     *                  property="meta",
     *                  type="object",
     *                  @OA\Property(property="current_page", type="integer"),
     *                  @OA\Property(property="per_page", type="integer"),
     *                  @OA\Property(property="total", type="integer"),
     *                  @OA\Property(property="last_page", type="integer")
     *              )
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
    public function __invoke(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $list = $this->service->listForUser($request->user(), $perPage);

        return response()->json([
            'data' => CounterpartyResource::collection($list->items()),
            'meta' => [
                'current_page' => $list->currentPage(),
                'per_page' => $list->perPage(),
                'total' => $list->total(),
                'last_page' => $list->lastPage(),
            ],
        ]);
    }
}
