<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Esen"),
     *                 @OA\Property(property="phone_number", type="string", example="65123456"),
     *                 @OA\Property(property="email", type="string", example="info@tds.gov.tm"),
     *                 @OA\Property(property="password", type="string", example="password"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User creation response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(UserStoreRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['password'] = Hash::make($validatedData['password']);

        // Handle image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
            $validatedData['image'] = $imagePath;
        } elseif (isset($validatedData['image']) && Str::startsWith($validatedData['image'], 'data:image')) {
            $imagePath = $this->uploadBase64Image($validatedData['image']);
            $validatedData['image'] = $imagePath;
        }

        $user = User::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => new UserResource($user)
        ], 200);
    }

     /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update an existing user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Esen"),
     *             @OA\Property(property="phone_number", type="string", example="64123456"),
     *             @OA\Property(property="email", type="string", example="info@tds.gov.tm"),
     *             @OA\Property(property="password", type="string", example="passwordupdated"),
     *             @OA\Property(property="image", type="string", example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ"),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(UserUpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            $validatedData = $request->validated();

            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            // Handle image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('public/user_images', $fileName);
                $validatedData['image'] = Storage::url($filePath);
            } elseif (isset($validatedData['image']) && Str::startsWith($validatedData['image'], 'data:image')) {
                $validatedData['image'] = $this->uploadBase64Image($validatedData['image']);
            } else {
                throw new \Exception('Invalid image data');
            }

            $user->update($validatedData);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => new UserResource($user)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload base64 image
     *
     * @param string $base64Image
     * @return string
     */
    private function uploadBase64Image($base64Image)
    {
        // Extract the base64 encoded text without the prefix
        $image_parts = explode(";base64,", $base64Image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);

        $fileName = uniqid() . '.' . $image_type;
        $filePath = 'public/user_images/' . $fileName;

        // Save file
        Storage::put($filePath, $image_base64);

        // Return the public URL
        return Storage::url($filePath);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User details response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $user = User::with('shop')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'user' => new UserResource($user)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/me",
     *     summary="Get authenticated user's information",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return new UserResource($user);
    }
}