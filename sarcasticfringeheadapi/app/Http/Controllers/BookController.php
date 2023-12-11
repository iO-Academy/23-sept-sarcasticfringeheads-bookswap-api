<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    private Book $book;
    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    private function serializeAll($books) : array
    {
        $output = [];
        foreach ($books as $book) {
            $output[] = [
                "id" => $book->id,
                "claimed" => $book->claimed,
                "title" => $book->title,
                "author" => $book->author,
                "image" => $book->image,
                "genre" => [
                    "id" => $book->genre->id, 
                    "name" => $book->genre->name
                ],
            ];
    }
    return $output;
}

private function serializeSingle($book) : array
    {
            $output = [
                "id" => $book->id,
                "claimed" => $book->claimed,
                "title" => $book->title,
                "author" => $book->author,
                "image" => $book->image,
                "genre" => [
                    "id" => $book->genre->id, 
                    "name" => $book->genre->name
                ],
                "reviews" => $book->reviews
                ];
                return $output; 
    }


    public function getAllBooks(Request $request) : JsonResponse
    {
        $books = $this->book->all();
        foreach($books as $book)
        {
            $book->genre;
        }
        $serialized_books = $this->serializeAll($books);
        if (count($serialized_books) === 0)
        {
            return response()->json([
                'message' => 'No Books Found'
            ], 404);
        }
        return response()->json(["message" => "Books successfully retrieved", "data" => $serialized_books], 200);
    }

    public function getSingleBookById (Request $request, $id) : JsonResponse
    {
        $book = $this->book->find($id);
        if (!$book) {
            return response()->json([
                'message' => "Sorry, that book does not exist"
            ], 404);
        }
        $book->genre;
        $book->reviews;
        $serialized_books = $this->serializeSingle($book);
        return response()->json([
            'message'=> 'Success',
            'data' => $serialized_books
        ], 200);
    }
}