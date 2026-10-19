<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $books = Book::with('author', 'categories')
            ->when($request->query('search'), function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhereHas('author', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($request->query('category_id'), function (Builder $query, string $categoryId): void {
                $query->whereHas('categories', function (Builder $query) use ($categoryId): void {
                    $query->where('categories.id', $categoryId);
                });
            })
            ->when($request->boolean('in_stock'), function (Builder $query): void {
                $query->where('stock', '>', 0);
            })
            ->get();

        return response()->json($books);
    }

    /**
     * Display a listing of books whose stock is running out (< 5).
     */
    public function lowStock(): JsonResponse
    {
        return response()->json(Book::with('author', 'categories')->where('stock', '<', 5)->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'published_year' => 'required|integer|min:1900|max:'.date('Y'),
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'author_id' => 'required|exists:authors,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $book = Book::create(Arr::except($validated, 'category_ids'));
        $book->categories()->attach($validated['category_ids']);

        return response()->json($book->load('author', 'categories'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json($book->load('author', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);

        $book->update($validated);

        return response()->json($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
