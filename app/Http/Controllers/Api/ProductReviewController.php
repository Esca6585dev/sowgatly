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

        $reviews = ProductReview::with('user:id,name')
            ->where('product_id', $productId)
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'author' => $review->user ? $review->user->name : null,
                    'user_id' => $review->user_id,
                    'created_at' => $review->created_at,
                ];
            });

        $stats = ProductReview::where('product_id', $productId)
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        // Guests may read reviews (public catalog); they can never review.
        $userId = $request->user()?->id;

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'meta' => [
                'count' => (int) $stats->count,
                'average' => $stats->average !== null ? round((float) $stats->average, 1) : null,
                'can_review' => $userId !== null && $this->hasOrdered($userId, $productId),
                'my_review' => $userId !== null
                    ? ProductReview::where('product_id', $productId)->where('user_id', $userId)->first()
                    : null,
            ],
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

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
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

        $review = ProductReview::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['rating' => $request->input('rating'), 'comment' => $request->input('comment')]
        );

        return response()->json(['success' => true, 'data' => $review], 201);
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
