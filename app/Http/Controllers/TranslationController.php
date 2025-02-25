<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    /**
     * Get all translations with optional filtering.
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function index(Request $request)
    {
        // Initialize the query builder for translations
        $query = DB::table('translations')
            ->select('id', 'group', 'key', 'locale', 'value'); // Select only required columns

        // Apply filtering based on request parameters
        if ($request->has('tag')) {
            $query->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('translation_tag')
                    ->whereColumn('translation_tag.translation_id', 'translations.id')
                    ->where('translation_tag.tag_id', DB::table('tags')
                        ->where('name', $request->input('tag'))
                        ->pluck('id')
                    );
            });
        }

        if ($request->has('key')) {
            $query->where('key', 'like', '%' . $request->input('key') . '%');
        }

        if ($request->has('content')) {
            $query->where('value', 'like', '%' . $request->input('content') . '%');
        }

        // Use cursor for memory efficiency when fetching large data
        $translations = $query->cursor();

        // Stream response to optimize memory usage
        return response()->stream(function () use ($translations) {
            echo '[';
            $first = true;
            foreach ($translations as $translation) {
                if (!$first) {
                    echo ',';
                }
                $first = false;
                echo json_encode($translation);
            }
            echo ']';
        }, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Store a new translation in the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request data
        $validatedData = $request->validate([
            'group' => 'required|string|max:255',
            'key' => 'required|string|max:255',
            'locale' => 'required|string|size:5',
            'value' => 'required|string',
        ]);

        // Create a new translation entry
        $translation = Translation::create($validatedData);

        return response()->json($translation, 201);
    }

    /**
     * Retrieve a specific translation by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        return response()->json($translation, 200);
    }

    /**
     * Update an existing translation by ID.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Find the translation by ID
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        // Validate incoming request data
        $validatedData = $request->validate([
            'group' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:255',
            'locale' => 'nullable|string|size:5',
            'value' => 'nullable|string',
        ]);

        // Update the translation record
        $translation->update($validatedData);

        return response()->json($translation, 200);
    }

    /**
     * Delete a translation by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // Find the translation by ID
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }

        // Delete the translation record
        $translation->delete();

        return response()->json(null, 204);
    }

    /**
     * Export translations into a structured JSON format.
     *
     * @return JsonResponse
     */
    public function export(): JsonResponse
    {
        try {
            $result = [];

            // Fetch translations in chunks to optimize performance
            Translation::select('locale', 'group', 'key', 'value')
                ->chunk(1000, function ($translations) use (&$result) {
                    foreach ($translations as $translation) {
                        $result[$translation->locale][$translation->group][$translation->key] = $translation->value;
                    }
                });

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Translation export failed: ' . $e->getMessage());

            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Search translations based on the given request parameters.
     *
     * This method allows searching translations using tags, keys, content, and locale.
     * It utilizes full-text search for efficient searching and caches the results
     * for improved performance.
     *
     * @param Request $request The incoming HTTP request containing search filters.
     *
     * @return JsonResponse JSON response containing the search results.
     */
    public function search(Request $request): JsonResponse
    {
        // Start query with necessary fields
        $query = DB::table('translations')
            ->select('id', 'group', 'key', 'locale', 'value');

        // Optimize tag filtering with whereIn
        if ($request->has('tag')) {
            $tagId = DB::table('tags')
                ->where('name', $request->input('tag'))
                ->value('id'); // Fetch single tag ID

            if ($tagId) {
                $query->whereIn('id', function ($subquery) use ($tagId) {
                    $subquery->select('translation_id')
                        ->from('translation_tag')
                        ->where('tag_id', $tagId);
                });
            }
        }

        // Use Full-Text Search if enabled, otherwise fallback to LIKE
        if ($request->has('key')) {
            $key = $request->input('key');
            $query->whereRaw("MATCH(`key`) AGAINST (? IN BOOLEAN MODE)", [$key]);
        }

        if ($request->has('content')) {
            $content = $request->input('content');
            $query->whereRaw("MATCH(`value`) AGAINST (? IN BOOLEAN MODE)", [$content]);
        }

        if ($request->has('locale')) {
            $query->where('locale', $request->input('locale'));
        }

        // Optimize response speed using limit instead of pagination
        $translations = $query->limit(50)->get();

        // Cache results for repeated queries
        $cacheKey = 'search_' . md5(json_encode($request->all()));

        $cachedResults = Cache::remember(
            $cacheKey,
            60,
            static fn () => $translations
        );

        return response()->json($cachedResults, 200);
    }
}
