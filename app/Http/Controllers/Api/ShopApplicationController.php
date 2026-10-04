<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\ShopApplication;
use App\Rules\TurkmenistanPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(name="Shop applications", description="Requests to open a shop on Sowgatly")
 */
class ShopApplicationController extends Controller
{
    use RespondsWithJson;

    /**
     * @OA\Post(
     *     path="/api/shop-applications",
     *     summary="Apply to open a shop (guests and signed-in users)",
     *     tags={"Shop applications"},
     *     @OA\RequestBody(@OA\JsonContent(required={"name","phone"},
     *         @OA\Property(property="name", type="string", example="Gül dünýäsi"),
     *         @OA\Property(property="phone", type="string", example="65656585"),
     *         @OA\Property(property="region_id", type="integer", nullable=true, example=1),
     *         @OA\Property(property="description", type="string", nullable=true)
     *     )),
     *     @OA\Response(response="201", description="Application received"),
     *     @OA\Response(response="422", description="Validation error"),
     *     @OA\Response(response="429", description="Too many applications")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => ['required', new TurkmenistanPhoneNumber],
            'region_id' => 'nullable|integer|exists:regions,id',
            'description' => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $application = ShopApplication::create([
            'user_id' => optional($request->user('sanctum'))->id,
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'region_id' => $request->input('region_id'),
            'description' => $request->input('description'),
            'status' => 'new',
        ]);

        return $this->ok(['data' => $application], 'Application received', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/me/shop-applications",
     *     summary="The signed-in user's own shop applications",
     *     tags={"Shop applications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function mine(Request $request)
    {
        return $this->ok([
            'data' => ShopApplication::where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }
}
