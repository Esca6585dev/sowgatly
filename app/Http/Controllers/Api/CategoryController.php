<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;
use Str;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints of Categories"
 * )
 */
/**
 * @OA\Schema(
 *     schema="CategoryResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name_tm", type="string", example="Category Name TM"),
 *     @OA\Property(property="name_en", type="string", example="Category Name EN"),
 *     @OA\Property(property="name_ru", type="string", example="Category Name RU"),
 *     @OA\Property(property="category_id", type="integer", example=null),
 *     @OA\Property(property="image_url", type="string", example="http://localhost:8000/category/category-seeder/other_fancy_flowers.png"),
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *     summary="Get list of categories",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="parent",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(response="200", description="List of categories"),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->query('parent', false)) {
            $query->parentCategory();
        }

        $categories = $query->paginate(15);

        return CategoryResource::collection($categories);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}/subcategories",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *     summary="Get all subcategories by parent category ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="List of subcategories"),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function getSubcategories($id)
    {
        $subcategories = Category::where('category_id', $id)->get();

        return CategoryResource::collection($subcategories);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *     summary="Create a new category",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name_tm", type="string", example="test_tm"),
     *                 @OA\Property(property="name_en", type="string", example="test_en"),
     *                 @OA\Property(property="name_ru", type="string", example="test_ru"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file"
     *                 ),
     *                 @OA\Property(property="category_id", type="integer", example="1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201", 
     *         description="Category created",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name_tm' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $category = Category::create($validatedData);

        $this->uploadImage($category, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *     summary="Get a category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Category details"),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * @OA\Post(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *     summary="Update a category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name_tm", type="string", example="test_tm_updated"),
     *                 @OA\Property(property="name_en", type="string", example="test_en_updated"),
     *                 @OA\Property(property="name_ru", type="string", example="test_ru_updated"),
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file"
     *                 ),
     *                 @OA\Property(property="category_id", type="integer", example="2")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200", 
     *         description="Category updated",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'name_tm' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:10240',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        // Update category data
        $category->update($validatedData);

        // Handle image upload
        if ($request->hasFile('image')) {
            $this->uploadImage($category, $request->file('image'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
        ]);
    }

    /**
     * Upload and save the category image.
     *
     * @param Category $category
     * @param \Illuminate\Http\Uploadednull $image
     * @return void
     */
    protected function uploadImage(Category $category, $image)
    {
        if ($image) {
            $date = date("d-m-Y H-i-s");
            $fileRandName = Str::random(10);
            $fileExt = $image->getClientOriginalExtension();

            $fileName = $fileRandName . '.' . $fileExt;
            
            $path = 'category/' . Str::slug($category->name_en . '-' . $date ) . '/';

            $image->move(public_path($path), $fileName);
            
            $originalImage = $path . $fileName;

            // Delete old image if exists
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }

            $category->image = $originalImage;
            $category->save();
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     security={{"sanctum":{}}},
     *     summary="Delete a category",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Category deleted"),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *     )
     * )
     */
    public function destroy(Category $category, $lang = null)
    {
        $this->deleteFolder($category);

        $category->delete();

        return response(null, 204);
    }

    /**
     * Delete the image associated with the shop.
     *
     * @param Category $category
     * @return void
     */
    public function deleteImage(Category $category)
    {
        if ($category->image) {
            $imagePath = public_path($category->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }
    }
}