<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Author::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authors',
            'bio' => 'nullable|string',
        ]);

        $author = Author::create($validated);

        return response()->json($author, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author): JsonResponse
    {
        return response()->json($author->load('books'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('authors')->ignore($author)],
            'bio' => 'nullable|string',
        ]);

        $author->update($validated);

        return response()->json($author);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author): JsonResponse
    {
        $deletedBooksCount = $author->books()->count();

        $author->delete();

        return response()->json([
            'message' => 'Penulis berhasil dihapus beserta '.$deletedBooksCount.' buku terkait',
            'deleted_books_count' => $deletedBooksCount,
        ]);
    }
}
