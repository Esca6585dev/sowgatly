<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductReviewController extends Controller
{
    public function index(Request $request, $productId)
    {
        if (!Product::whereKey($productId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $query = ProductReview::with('user:id,name,image')
            ->where('product_id', $productId)
            ->latest();

        // Old clients get the plain 50-item list; `page` switches to pagination.
        $paginator = $request->filled('page') ? $query->paginate(20) : null;
        $collection = $paginator ? $paginator->getCollection() : $query->limit(50)->get();

        $reviews = $collection->map(function ($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'rating_match' => $review->rating_match,
                'rating_value' => $review->rating_value,
                'rating_service' => $review->rating_service,
                'comment' => $review->comment,
                'author' => $review->user ? $review->user->name : null,
                'user_id' => $review->user_id,
                'user' => $review->user ? [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'image' => $review->user->image ? asset($review->user->image) : null,
                ] : null,
                'order_id' => $review->order_id,
                'created_at' => $review->created_at,
            ];
        })->values();

        $stats = ProductReview::where('product_id', $productId)
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        // Guests may read reviews (public catalog); they can never review.
        $userId = $request->user()?->id;

        $meta = [
            'count' => (int) $stats->count,
            'average' => $stats->average !== null ? round((float) $stats->average, 1) : null,
            'can_review' => $userId !== null && $this->hasOrdered($userId, $productId),
            'my_review' => $userId !== null
                ? ProductReview::where('product_id', $productId)->where('user_id', $userId)->first()
                : null,
        ];

        if ($paginator) {
            $meta += [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'meta' => $meta,
        ]);
    }

    /**
     * Create or replace the signed-in user's review. Only customers who
     * have ordered the product (and not had that order cancelled) may
     * review it.
     */
    public function store(Request $request, $productId)
    {
        if (!Product::whereKey($productId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // `rating` may be omitted when the three criteria are all given: it is
        // then their rounded average, so old clients and the new app both work.
        $validator = Validator::make($request->all(), [
            'rating' => 'required_without_all:rating_match,rating_value,rating_service|nullable|integer|min:1|max:5',
            'rating_match' => 'nullable|integer|min:1|max:5|required_with:rating_value,rating_service',
            'rating_value' => 'nullable|integer|min:1|max:5|required_with:rating_match,rating_service',
            'rating_service' => 'nullable|integer|min:1|max:5|required_with:rating_match,rating_value',
            'comment' => 'nullable|string|max:1000',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userId = $request->user()->id;

        if (!$this->hasOrdered($userId, $productId)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products you have ordered',
            ], 403);
        }

        $orderId = $request->input('order_id');
        if ($orderId !== null && !$this->orderContains($userId, (int) $orderId, $productId)) {
            return response()->json([
                'success' => false,
                'message' => 'This order does not contain the product',
            ], 403);
        }

        $criteria = collect(ProductReview::CRITERIA)
            ->mapWithKeys(fn ($key) => [$key => $request->filled($key) ? (int) $request->input($key) : null]);

        $rating = $request->filled('rating')
            ? (int) $request->input('rating')
            : (int) round($criteria->filter()->avg());

        $review = ProductReview::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            $criteria->all() + [
                'rating' => $rating,
                'comment' => $request->input('comment'),
                'order_id' => $orderId,
            ]
        );

        return response()->json(['success' => true, 'data' => $review], 201);
    }

    /** The order belongs to the caller, is not cancelled and contains the product. */
    private function orderContains(int $userId, int $orderId, $productId): bool
    {
        return Order::whereKey($orderId)
            ->where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', fn ($q) => $q->where('product_id', $productId))
            ->exists();
    }

    private function hasOrdered($userId, $productId): bool
    {
        return Order::where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->exists();
    }
}
