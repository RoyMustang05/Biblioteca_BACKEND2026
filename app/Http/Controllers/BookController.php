<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct() {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Book::class);
        $books = Book::when($request->has('title'), function ($query) use ($request) {
            $query->where('title', 'like', '%'.$request->input('title').'%');
        })->when($request->has('isbn'), function ($query) use ($request) {
            $query->where('ISBN', 'like', '%'.$request->input('isbn').'%');
        })->when($request->has('is_available'), function ($query) use ($request) {
            $query->where('is_available', $request->boolean('is_available'));
        })
            ->paginate();

        return response()->json(BookResource::collection($books));
    }

    public function show(Book $book)
    {
        $this->authorize('view', $book);

        return response()->json(BookResource::make($book));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Book::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'ISBN' => ['required', 'string', 'max:50', 'unique:books,ISBN'],
            'total_copies' => ['required', 'integer', 'min:1'],
            'available_copies' => ['required', 'integer', 'min:0', 'lte:total_copies'],
        ]);

        $data['is_available'] = $data['available_copies'] > 0;

        $book = Book::create($data);

        return response()->json(BookResource::make($book), 201);
    }

    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'ISBN' => ['required', 'string', 'max:50', 'unique:books,ISBN,'.$book->id],
            'total_copies' => ['required', 'integer', 'min:1'],
            'available_copies' => ['required', 'integer', 'min:0', 'lte:total_copies'],
        ]);

        $data['is_available'] = $data['available_copies'] > 0;

        $book->update($data);

        return response()->json(BookResource::make($book->fresh()));
    }

    public function partialUpdate(Request $request, Book $book)
    {
        $this->authorize('update', $book);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'ISBN' => ['sometimes', 'string', 'max:50', 'unique:books,ISBN,'.$book->id],
            'total_copies' => ['sometimes', 'integer', 'min:1'],
            'available_copies' => ['sometimes', 'integer', 'min:0'],
        ]);

        $totalCopies = (int) ($data['total_copies'] ?? $book->total_copies);
        $availableCopies = (int) ($data['available_copies'] ?? $book->available_copies);

        if ($availableCopies > $totalCopies) {
            return response()->json([
                'message' => 'available_copies cannot be greater than total_copies',
            ], 422);
        }

        $data['is_available'] = $availableCopies > 0;

        $book->update($data);

        return response()->json(BookResource::make($book->fresh()));
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
