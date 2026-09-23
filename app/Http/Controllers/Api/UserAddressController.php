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
    public function index(Request $request)
    {
        $addresses = UserAddress::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $addresses]);
    }

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
