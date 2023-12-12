<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    private Book $book;
    private Genre $genre;
    public function __construct(Book $book, Genre $genre)
    {
        $this->book = $book;
        $this->genre = $genre;
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
                "reviews" => $book->reviews,
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
        $request->claimed;

        if ($request->claimed)
        {
            if ($request->claimed === 1)
            {
                $books = $books->where('claimed', 1);
            }
        
            else if ($request->claimed === 0)
            {
                $books = $books->where('claimed', 0);
            }
        }
        
        if ($request->genre && $request->genre > 0 && $request->genre <= $this->genre->count())
        {
            $books = $books->where('genre_id', $request->genre);
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
                'message' => "Book with id $id not found"
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



    public function claimABook(Request $request, $id)
    {
        // Validate the request data
            $request->validate([
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
        if ($book->claimed === 1)
        {
            return response()->json([
                'message' => "Book 10 is already claimed"
            ], 400);
        }

        // Update the book details
        $book->user_name = $request->input('name');
        $book->user_email = $request->input('email');
        $book->claimed = 1; 
        $book->save();
        return response()->json(['message' => 'Book claimed successfully']);
    }



    
    public function UnclaimABook(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);
        // Find the book by ID
        $book = Book::find($id);
        // Check if the book exists
        if (!$book) {
            return response()->json([
                'message' => "Sorry, that book does not exist"
            ], 404);
        
        } else {

            $enteredEmail = $request->input('email');
            $correctEmail = $book->user_email;

            if ($enteredEmail !== $correctEmail) {
                return response()->json([
                    'message' => "Sorry, the email does not match"
                ], 404);    
        }
            // Update the book details
            $book->user_name = 'NULL';
            $book->user_email ='NULL';
            $book->claimed = 0; // Set the claimed column to 1
            $book->save();
            
            return response()->json(['message' => 'Book returned successfully']);


            }
     
    }
}

