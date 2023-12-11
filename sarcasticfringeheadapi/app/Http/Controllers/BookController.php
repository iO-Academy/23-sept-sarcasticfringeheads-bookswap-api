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
            ];
    
    return $output;
}

    public function getAllBooks(Request $request) : JsonResponse
    {
        // get all books tick
        // does user want claimed or unclaimed
        // filter books to reflect
        // display

        $books = $this->book->with('genre')->get();
        $claimedFilter = $request->claimed;

        if (isset($request->claimed) && $claimedFilter == 1) 
        {
            $books = $books->where('claimed', $claimedFilter);
        }
        else 
        {
            $books = $books->where('claimed', 0);
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
        $serialized_books = $this->serializeSingle($book);
        return response()->json([
            'message'=> 'Success',
            'data' => $serialized_books
        ], 200);
    }



    public function ClaimABook(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);
        // Find the book by ID
        $book = Book::find($id);
        // Check if the book exists
        if (!$book) {
            return response()->json([
                'message' => "Sorry, that book does not exist"
            ], 404);
        }

        // Update the book details
        $book->user_name = $request->input('name');
        $book->user_email = $request->input('email');
        $book->claimed = 1; // Set the claimed column to 1
        $book->save();
        return response()->json(['message' => 'Book claimed successfully']);
    }
}

