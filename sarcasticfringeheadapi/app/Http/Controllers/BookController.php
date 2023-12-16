<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;


// **TODO**

//check genre:exists for adding a book -> done

//if search doesnt return anything, DO NOT TRY TO FIND THE RELATED REVIEWS AND GENRES. (dont link on null) -> done
class BookController extends Controller
{
    private Book $book;
    private Genre $genre;
    private Carbon $carbon;
    public function __construct(Book $book, Genre $genre, Carbon $carbon)
    {
        $this->book = $book;
        $this->genre = $genre;
        $this->carbon = $carbon;
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
                "claimed_by_name" => $book->user_name,
                "year" => $book->year,
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
        $request->validate([
            'genre' => 'exists:genres,id',
            'claimed' => 'integer|min:0|max:1',
            'search' => 'string|min:0|max:1000|nullable'
        ]);

        $query = $this->book->query();


        if ($request->claimed)
        {
            $query = $query->where('claimed', $request->claimed);
        }

        if ($request->genre)
        {
            $query = $query->where('genre_id', $request->genre);
        }

        function addSearchToQuery (Builder $q, string $term) 
        {
            $q = $q->where('title','like','%' . $term . '%')->orWhere('author','like', '%' . $term . '%')->orWhere('blurb', 'like', '%' . $term . '%');
        }

        if ($request->search)
        {
            addSearchToQuery($query, $request->search);
        }
    
        
        $books = $query->get();
        if (count($books) === 0)
        {
            return response()->json([
                'message' => 'No Books Found'
            ], 404);
        }

        $serialized_books = $this->serializeAll($books);
        
    
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
        $book = $this->book->find($id);
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
        $book = $this->book->find($id);
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
            'title'=> 'string|required|max:255',
            'author'=> 'string|required|max:255',
            'genre_id'=> 'integer|required|exists:genres,id',
            'blurb'=> 'string|max:2000',
            'image'=> 'url|max:1000',
            'year'=> 'integer|max:' . $this->carbon->now()->year
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

    public function createBookReview(Request $request) {
        $request->validate([
            'name'=>'string|required|max:255',
            'rating'=>'integer|required|max:5',
            'review'=>'string|required|max:5000',
            'book_id'=> 'integer|required|max:5|exists:books,id'
        ]);

        $newReview = new Review();
        $newReview->name = $request->name;
        $newReview->rating = $request->rating;
        $newReview->review = $request->review;
        $newReview->book_id = $request->book_id;
        $newReview->save();

        if ($newReview){
            return response()->json(['message' => 'Review created'], 201);
        }
        return response()->json(["message"=> "Unexpected error occurred"], 500);
    }
}
