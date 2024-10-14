<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Device;
use App\Models\UserOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Rules\TurkmenistanPhoneNumber;
use App\Http\Resources\UserResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\DeviceResource;
use Str;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints of Authentication"
 * )
 */
class AuthOtpController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/otp/generate",
     *     summary="Generate OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"phone_number"},
     *                 @OA\Property(property="phone_number", type="string", example="65656585")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP generation response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP generated successfully"),
     *         )
     *     )
     * )
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', new TurkmenistanPhoneNumber],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        try {
            $userOtp = $this->generateOtp($request->phone_number);

            if ($userOtp) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP has been sent on Your Mobile Number.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Generate OTP for the given phone number
     *
     * @param string $phone_number
     * @return UserOtp|null
     * @throws \Exception
     */
    protected function generateOtp($phone_number)
    {
        DB::beginTransaction();
        try {
            // Find the user by phone number
            $user = User::where('phone_number', $phone_number)->first();

            // If user not found, throw an exception
            if (!$user) {
                throw new \Exception('User not found with the provided phone number.');
            }

            // Check for existing OTP
            $userOtp = UserOtp::where('user_id', $user->id)->latest()->first();
            $now = now();

            if ($userOtp && $now->isBefore($userOtp->expire_at)) {
                DB::commit();
                return $userOtp;
            }

            // Create a new OTP
            $newOtp = UserOtp::create([
                'user_id' => $user->id,
                'otp' => "0000", // Set default OTP to "0000" // str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT), // Generate a random 4-digit OTP
                'expire_at' => $now->addMinutes(10)
            ]);

            DB::commit();
            return $newOtp;
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error
            \Log::error('Error in generateOtp: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login with OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"phone_number", "otp"},
     *                 @OA\Property(property="phone_number", type="string", example="65656585"),
     *                 @OA\Property(property="otp", type="string", example="0000")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="access_token", type="string", example="1|MADVetcOYwHT7yYmWWQB9PLK6T1lQyvoBYI8Pqc559492981"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="phone_number", type="string", example="65656585"),
     *                 @OA\Property(property="email", type="string", example="tds@sanly.tm"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(
     *                 property="shops",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="My Shop"),
     *                     @OA\Property(property="description", type="string", example="A great shop")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function loginWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', new TurkmenistanPhoneNumber],
            'otp' => 'required|digits:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all(),
            ], 200);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 200);
        }

        $userOtp = UserOtp::where('otp', $request->otp)
                        ->where('user_id', $user->id)
                        ->latest()
                        ->first();

        $now = now();
        if (!$userOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Your OTP is not correct!',
            ], 200);
        } else if ($now->isAfter($userOtp->expire_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Your OTP has been expired!',
            ], 200);
        }

        $userOtp->update([
            'expire_at' => now()
        ]);

        if ($request->header('Device') == 'Mobile') {
            Device::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => $request->device_token]
            );
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // Debug: Check if user has shops
        \Log::info('User shops: ' . json_encode($user->shops));

        $shops = $user->shops;
        if ($shops === null) {
            $shops = collect(); // Create an empty collection if shops is null
        }

        try {
            return response()->json([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
                'shops' => ShopResource::collection($shops),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in loginWithOtp: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 200);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register with OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"phone_number", "name"},
     *                 @OA\Property(property="phone_number", type="string", example="65656565"),
     *                 @OA\Property(property="name", type="string", example="Esen Meredow"),
     *                 @OA\Property(property="email", type="string", example="esca6585@modahouse.top")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration successful"),
     *             @OA\Property(property="access_token", type="string", example="1|MADVetcOYwHT7yYmWWQB9PLK6T1lQyvoBYI8Pqc559492981"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Esen Meredow"),
     *                 @OA\Property(property="phone_number", type="string", example="65656565"),
     *                 @OA\Property(property="email", type="string", example="esca6585@modahouse.top"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(
     *                 property="shops",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="device",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="device_id", type="string", example="device_unique_id"),
     *                 @OA\Property(property="device_type", type="string", example="android"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function registerWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', new TurkmenistanPhoneNumber, 'unique:users'],
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'phone_number' => $request->phone_number,
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $otpCode = "0000"; // For testing purposes, use a fixed OTP

            UserOtp::create([
                'user_id' => $user->id,
                'otp' => $otpCode,
                'expire_at' => now()->addMinutes(10),
            ]);

            // Detect device type
            $deviceType = $this->detectDeviceType($request);

            // Generate a unique device token
            $deviceToken = Str::random(64);

            $device = Device::create([
                'user_id' => $user->id,
                'device_token' => $deviceToken,
                'device_type' => $deviceType
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'otp' => $otpCode,
                'user' => new UserResource($user),
                'shops' => $user->shops ? ShopResource::collection($user->shops) : [],
                'device' => new DeviceResource($device),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in registerWithOtp: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 200);
        }
    }

    private function detectDeviceType(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        
        if (strpos($userAgent, 'Android') !== false) {
            return 'android';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            return 'ios';
        } elseif (strpos($userAgent, 'Windows') !== false) {
            return 'windows';
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            return 'macos';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            return 'linux';
        } else {
            return 'web';
        }
    }
     /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        return $request->user()->tokens();
        try {
            // Check if the user is authenticated
            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 200);
            }

            // Attempt to revoke the token
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage(),
            ], 200);
        }
    }
}