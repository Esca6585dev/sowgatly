<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use RespondsWithJson;

    /**
     * @OA\Get(
     *     path="/api/banners",
     *     summary="Active promo banners for the home screen",
     *     tags={"Home"},
     *     @OA\Parameter(name="region_id", in="query", required=false, description="City; global banners are always included", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Banners ordered by position",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BannerResource")))
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $regionId = $request->filled('region_id') ? (int) $request->query('region_id') : null;

        $banners = Banner::visible($regionId)->get();

        return $this->ok(['data' => BannerResource::collection($banners)]);
    }
}
