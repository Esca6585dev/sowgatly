<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Composition;
use App\Http\Resources\CompositionResource;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Compositions",
 *     description="API Endpoints of Compositions"
 * )
 */
class CompositionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/compositions",
     *     summary="Get all compositions",
     *     tags={"Compositions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation")
     * )
     */
    public function index()
    {
        return CompositionResource::collection(Composition::all());
    }

    /**
     * @OA\Post(
     *     path="/api/compositions",
     *     summary="Create a new composition",
     *     tags={"Compositions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Successful operation")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $composition = Composition::create($validated);

        return new CompositionResource($composition);
    }

    /**
     * @OA\Get(
     *     path="/api/compositions/{id}",
     *     summary="Get a specific composition",
     *     tags={"Compositions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="404", description="Composition not found")
     * )
     */
    public function show(Composition $composition)
    {
        return new CompositionResource($composition);
    }

    /**
     * @OA\Put(
     *     path="/api/compositions/{id}",
     *     summary="Update a composition",
     *     tags={"Compositions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="404", description="Composition not found")
     * )
     */
    public function update(Request $request, Composition $composition)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $composition->update($validated);

        return new CompositionResource($composition);
    }

    /**
     * @OA\Delete(
     *     path="/api/compositions/{id}",
     *     summary="Delete a composition",
     *     tags={"Compositions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Successful operation"),
     *     @OA\Response(response="404", description="Composition not found")
     * )
     */
    public function destroy(Composition $composition)
    {
        $composition->delete();

        return response()->noContent();
    }
}