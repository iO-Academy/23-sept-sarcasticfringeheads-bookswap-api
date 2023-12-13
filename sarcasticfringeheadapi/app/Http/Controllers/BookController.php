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
                "blurb" => $book->blurb,
                "year" => $book->year,
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
        $request->validate([
            'genre' => 'exists:genres,id',
            'claimed' => 'integer|min:0|max:1',
            'search' => 'string |min:0|max:1000|nullable'
        ]);

        $query = $this->book->query();

        if ($request->search)
        {
            $query = $query->where('title','like','%' . $request->search . '%')->orWhere('author','like', '%' . $request->search . '%')->orWhere('blurb', 'like', '%' . $request->search . '%');
        }
        
        if ($request->claimed)
        {
            $query = $query->where('claimed', $request->claimed);
        }

        if ($request->genre)
        {
            $query = $query->where('genre_id', $request->genre);
        }
        
        $books = $query->get();
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
                'message' => "Book $id is already claimed"
            ], 400);
        }

        // Update the book details
        $book->user_name = $request->name;
        $book->user_email = $request->email;
        $book->claimed = 1; 
        $book->save();
        return response()->json(['message' => 'Book claimed successfully']);
    }

    
    public function UnclaimABook(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
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

    public function addABook (Request $request) {
        // post request 
        $request->validate([
            'title'=> 'required|max:255',
            'author'=> 'required|max:255',
            'genre_id'=> 'required|max:20',
            'blurb'=> 'max:2000',
            'image'=> 'max:1000',
            'year'=> 'max:11'
        ]);
        
        $newBook = new Book();
        $newBook->title = $request->title;
        $newBook->author = $request->author;
        $newBook->blurb= $request->blurb;
        $newBook->genre_id = $request->genre_id;
        $newBook->image = $request->image;
        $newBook->year = $request->year;
        $newBook->claimed = 0;

        $newBook->save();
        
        if (!$newBook){
            return response()->json(['message' => 'Unexpected error occured'], 500);
        }
        return response()->json(['message' => 'Book created'], 201);
        

        // create new object book
        // what fields need to be included
        // include title, author, genre, year, (image/blurb optional?)
        // $newBook->title = $request->"title" ?
        // Validate the input
        // save the book
        // success and error responses
    }
}

