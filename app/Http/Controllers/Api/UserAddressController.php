<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Delivery addresses saved by the signed-in customer. (The `addresses`
 * resource is a shop's own address and is unrelated.)
 */
class UserAddressController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/me/addresses",
     *     summary="The caller's delivery addresses (default first)",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Addresses", @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="data", type="array", @OA\Items(
     *         @OA\Property(property="id", type="integer"), @OA\Property(property="title", type="string", nullable=true), @OA\Property(property="address", type="string"), @OA\Property(property="is_default", type="boolean")))))
     * )
     */
    public function index(Request $request)
    {
        $addresses = UserAddress::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $addresses]);
    }

    /**
     * @OA\Post(
     *     path="/api/me/addresses",
     *     summary="Add a delivery address (the first one becomes the default)",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(required={"address"}, @OA\Property(property="title", type="string", maxLength=100, nullable=true, example="Home"), @OA\Property(property="address", type="string", maxLength=500, example="Aşgabat, Parahat 7"), @OA\Property(property="is_default", type="boolean"))),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request);
        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $userId = $request->user()->id;
        $isFirst = !UserAddress::where('user_id', $userId)->exists();

        $address = DB::transaction(function () use ($request, $userId, $isFirst) {
            $makeDefault = $isFirst || $request->boolean('is_default');
            if ($makeDefault) {
                UserAddress::where('user_id', $userId)->update(['is_default' => false]);
            }

            return UserAddress::create([
                'user_id' => $userId,
                'title' => $request->input('title'),
                'address' => $request->input('address'),
                'is_default' => $makeDefault,
            ]);
        });

        return response()->json(['success' => true, 'data' => $address], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/me/addresses/{id}",
     *     summary="Edit a delivery address",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="title", type="string", nullable=true), @OA\Property(property="address", type="string"), @OA\Property(property="is_default", type="boolean"))),
     *     @OA\Response(response="200", description="Updated"),
     *     @OA\Response(response="404", description="Address not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->find($id);
        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        $validator = $this->validator($request);
        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        DB::transaction(function () use ($request, $address) {
            if ($request->boolean('is_default')) {
                UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
            }

            $address->update([
                'title' => $request->input('title'),
                'address' => $request->input('address'),
                'is_default' => $request->boolean('is_default') || $address->is_default,
            ]);
        });

        return response()->json(['success' => true, 'data' => $address->fresh()]);
    }

    /**
     * @OA\Delete(
     *     path="/api/me/addresses/{id}",
     *     summary="Delete a delivery address (another one becomes default if needed)",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Deleted"),
     *     @OA\Response(response="404", description="Address not found")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)->find($id);
        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $address->delete();

            // Keep one default address while any remain.
            if ($wasDefault) {
                $next = UserAddress::where('user_id', $address->user_id)->latest()->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    private function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'nullable|string|max:100',
            'address' => 'required|string|max:500',
            'is_default' => 'sometimes|boolean',
        ]);
    }

    private function validationError($validator)
    {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }
}
