<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    private Book $book;
    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    public function getAllBooks(Request $request)
    {
        $books = $this->book->query();
        $books = $books->get();
        foreach($books as $book)
        {
            $book->genre;
        }

        if (count($books) === 0)
        {
            return response()->json([
                'message' => 'No Books Found'
            ]);
        }
        return response()->json(["message" => "Books successfully retrieved", "data" => $books], 200);
        //may need to edit $books variable to delete multiple instances of genre_id if not handled by laravel automatically. (because book table has genre_id and genre table has id)
    }

    public function getSingleBookById (Request $request, $id)
    {
        $book = $this->book->find($id);
        if (!$book) {
            return response()->json([
                'message' => "Sorry, that book does not exist"
            ], 404);
        }
        return response()->json([
            'message'=> 'Success',
            'data' => $book
        ], 200);
    }
}

















    /*
    ** dont use yet. part of later stories. ** 
    public function getAllBooksWithFiltering(Request $request)
    {
        $request->validate([
            'claimed' => 'nullable|boolean',
            'genre' => 'nullable|integer',
            'search' => 'nullable|string'
        ]);

        $books = $this->book->query();

        if($request->has('claimed'))
        {
            if ($request->claimed)
            $books->where('claimed', $request->claimed);
        }
        if ($request->has('search'))
        {
            $searchTerm = $request->search;
            $books->where('name', 'like', '%'. $searchTerm .'%')
            ->orWhere('author', 'like', '%'. $searchTerm . '%');
        }
        if ($request->has('genre'))
        {
            $books->where('genre', $request->genre);
        }

        $books = $books->get();
        
        foreach($books as $book)
        {
            $book->genre;
        }

        if (count($books) === 0)
        {
            return response()->json([
                'message' => 'No Books Found'
            ]);
        }

        return response()->json(["message" => "Books successfully retrieved", "data" => $books], 200);
    }

    */
