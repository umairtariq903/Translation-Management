<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Retrieve all tags with optimized memory usage.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function index()
    {
        // Use cursor for efficient large dataset handling
        $tags = Tag::select('id', 'name')->cursor();

        // Stream JSON response to optimize memory usage
        return response()->stream(function () use ($tags) {
            echo '[';
            $first = true;
            foreach ($tags as $tag) {
                if (!$first) {
                    echo ',';
                }
                $first = false;
                echo json_encode($tag, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            echo ']';
        }, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Store a new tag in the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        // Create the tag
        $tag = Tag::create($validatedData);

        return response()->json($tag, 201);
    }

    /**
     * Retrieve a specific tag by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        return response()->json($tag, 200);
    }

    /**
     * Update an existing tag by ID.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        // Validate request data
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:tags,name,' . $id,
        ]);

        // Update the tag
        $tag->update($validatedData);

        return response()->json($tag, 200);
    }

    /**
     * Delete a tag by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $tag->delete();

        return response()->json(null, 204);
    }
}
