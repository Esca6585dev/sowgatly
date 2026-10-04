<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeFeed;
use App\Models\Product;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

/**
 * One call that fills the app home screen: root categories, banners and a
 * few product sections for the selected city.
 */
class HomeController extends Controller
{
    use RespondsWithJson;

    private const LOCALES = ['tm', 'ru', 'en'];

    /**
     * @OA\Get(
     *     path="/api/home",
     *     summary="Home screen feed: categories, banners and product sections for a city",
     *     tags={"Home"},
     *     @OA\Parameter(name="region_id", in="query", required=false, description="City to show products for; omit for all cities", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_section", in="query", required=false, description="Products per section, 1-20 (default 6)", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Feed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="region", type="object", nullable=true, @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")),
     *             @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/CategoryResource")),
     *             @OA\Property(property="banners", type="array", @OA\Items(ref="#/components/schemas/BannerResource")),
     *             @OA\Property(property="sections", type="array", @OA\Items(
     *                 @OA\Property(property="key", type="string", example="category:3"),
     *                 @OA\Property(property="title", type="object", @OA\Property(property="tm", type="string"), @OA\Property(property="ru", type="string"), @OA\Property(property="en", type="string")),
     *                 @OA\Property(property="category_id", type="integer", nullable=true),
     *                 @OA\Property(property="products", type="array", @OA\Items(ref="#/components/schemas/ProductResource"))
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $regionId = $request->filled('region_id') ? (int) $request->query('region_id') : null;
        $perSection = min(20, max(1, (int) $request->query('per_section', 6)));
        $locale = app()->getLocale();

        $payload = Cache::remember(
            HomeFeed::key($regionId, $locale, $perSection),
            HomeFeed::TTL_SECONDS,
            fn () => $this->build($regionId, $perSection)
        );

        return $this->ok($payload);
    }

    private function build(?int $regionId, int $perSection): array
    {
        $region = $regionId ? Region::find($regionId) : null;
        $regionIds = $regionId ? Region::selfAndDescendantIds($regionId) : null;

        $base = fn () => Product::query()
            ->storefront()
            ->when($regionIds, fn ($q) => $q->whereHas('shop', fn ($s) => $s->whereIn('region_id', $regionIds)))
            ->with(['category', 'shop', 'images'])
            ->withRatingSummary();

        $sections = [];

        $deliveryToday = $base()->deliveryToday()->latest()->limit($perSection)->get();
        if ($deliveryToday->isNotEmpty()) {
            $sections[] = $this->section('delivery_today', $this->titles('api.home_delivery_today'), $deliveryToday);
        }

        $popular = $base()->popular()->limit($perSection)->get();
        if ($popular->isNotEmpty()) {
            $sections[] = $this->section('popular', $this->titles('api.home_popular'), $popular);
        }

        $categories = Category::whereNull('category_id')->orderBy('id')->get();

        foreach ($categories as $category) {
            $descendants = $this->categoryIds($category);
            $products = $base()->whereIn('category_id', $descendants)->latest()->limit($perSection)->get();

            if ($products->count() >= 2) {
                $sections[] = $this->section(
                    'category:' . $category->id,
                    ['tm' => $category->name_tm, 'ru' => $category->name_ru, 'en' => $category->name_en],
                    $products,
                    $category->id
                );
            }
        }

        return [
            'region' => $region ? ['id' => $region->id, 'name' => $region->name] : null,
            'categories' => CategoryResource::collection($categories)->resolve(),
            'banners' => BannerResource::collection(Banner::visible($regionId)->get())->resolve(),
            'sections' => $sections,
        ];
    }

    /** The category itself plus its direct subcategories. */
    private function categoryIds(Category $category): array
    {
        return array_merge([$category->id], Category::where('category_id', $category->id)->pluck('id')->all());
    }

    private function section(string $key, array $title, $products, ?int $categoryId = null): array
    {
        $section = ['key' => $key, 'title' => $title];
        if ($categoryId !== null) {
            $section['category_id'] = $categoryId;
        }
        $section['products'] = ProductResource::collection($products)->resolve();

        return $section;
    }

    private function titles(string $langKey): array
    {
        $titles = [];
        foreach (self::LOCALES as $locale) {
            $titles[$locale] = Lang::get($langKey, [], $locale);
        }

        return $titles;
    }
}
