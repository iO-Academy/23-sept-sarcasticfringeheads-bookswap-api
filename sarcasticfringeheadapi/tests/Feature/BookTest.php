<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use Database\Factories\GenreFactory;
use SebastianBergmann\Type\VoidType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;

class BookTest extends TestCase
{
    use DatabaseMigrations;

    public function test_GetAllBookSuccess(): void
    {
        Book::factory()->create();
        Genre::factory(3)->create();

        $response = $this->getJson('/api/books');

        $response
            ->assertStatus(200)
            ->assertJson(function(AssertableJson $json){
                $json
                    ->hasAll(['message', 'data'])
                    ->has('data', 1, function(AssertableJson $json){
                        $json->hasAll(['id', 'claimed','title', 'author', 'image', 'genre'])
                        ->whereAllType([
                            'id'=> 'integer',
                            'claimed'=> 'integer',
                            'title'=>'string',
                            'author'=> 'string',
                            'image'=> 'string',
                            'genre'=>'array',
                            'genre.id'=>'integer',
                            'genre.name'=>'string'
                        ]);
                    });
            });
    }


public function test_GetAllBookFailure(): void
    {
        $response = $this->getJson('/api/books');
        $response->assertStatus(404)->assertJson(['message' => 'No Books Found']);
    }

    public function test_GetAllBookInvalidGenre(): void
    {
        Book::factory(1)->create();
        $response = $this->getJson('/api/books?genre=2');
        $response->assertStatus(422)->assertJson(['message' => 'The selected genre is invalid.', "errors" => ['genre' => ['The selected genre is invalid.']]]);
    }

    public function test_GetAllBookInvalidClaimed(): void
    {
        Book::factory(1)->create();
        $response = $this->getJson('/api/books?claimed=2');
        $response->assertStatus(422)->assertJson(['message' => 'The claimed field must not be greater than 1.']);
    }

    public function test_GetAllBookSearchSuccess(): void 
    {
        Book::factory(50)->create();
        $book = Book::factory()->create();
        $book->title = 'snozz';
        $book->save();
        
        $response = $this->getJson('/api/books?search=snozz');
        $response->assertJson(function(AssertableJson $json){
            $json->hasAll(['message', 'data'])->has('data', 1);
        });
    }

    public function test_SingleBookSuccess(): void
    {
        Book::factory()->create();

        $response = $this->getJson('/api/books/1');

        $response
            ->assertStatus(200)
            ->assertJson(function(AssertableJson $json){
                $json
                    ->hasAll(['message', 'data'])
                    ->has('data', function(AssertableJson $json){
                        $json->hasAll(['id', 'claimed','title', 'author', 'image', 'genre', 'year', 'blurb', 'reviews'])
                        ->whereAllType([
                            'id'=> 'integer',
                            'claimed'=> 'integer',
                            'title'=>'string',
                            'author'=> 'string',
                            'image'=> 'string',
                            'genre'=>'array',
                            'genre.id'=>'integer',
                            'genre.name'=>'string',
                            'blurb' => 'string',
                            'year' => 'integer',
                            'reviews' => 'array'
                        ]);
                    });
            });
    }

    public function test_SingleBookFailNoBooks(): void
    {
        $response = $this->getJson('/api/books/1');
        $response->assertStatus(404)->assertJson(['message' => 'Book with id 1 not found']);
    }

    public function test_SingleBookFailNonExistentBook(): void
    {
        Book::factory(3)->create();
        Genre::factory(3)->create();
        $response = $this->getJson('/api/books/4');
        $response->assertStatus(404)->assertJson(['message' => 'Book with id 4 not found']);
    }

    public function test_AddBookSuccess(): void
    {
        Genre::factory(1)->create();
        
        $book_data = [
            'title'=> 'Harry Pozza',
            'author' => 'JK Rolling',
            'blurb' => 'spelling test',
            'genre_id' => 1,
            'year' => 1999,
        ];
        
        $response = $this->postJson('/api/books', $book_data);
        $response->assertStatus(201)->assertJson(['message' => 'Book created']);

        $this->assertDatabaseHas('books', [
            'title'=> 'Harry Pozza',
            'author' => 'JK Rolling',
            'blurb' => 'spelling test',
            'genre_id' => 1,
            'year' => 1999,
        ]);
    }

    public function test_AddBookFailure(): void
    {
        Genre::factory(3)->create();
        
        $book_data = [];
        
        $response = $this->postJson('/api/books', $book_data);
        $response->assertInvalid(['title', 'author', 'genre_id']);

    }

    // claim:

    public function test_claimBookSuccess(): void 
    {
        Book::factory(1)->create();

        // edit a pre existing book from unclaimed to claimed 
        // then go and check if the book you claimed is claimed and that the user_name and user_email are in there
        //, and all other books are still unclaimed. 
        $data = [
            'name' => 'Elon Musk',
            'email' => 'elonmusk@tesla.com',
        ];
        $response = $this->putJson('api/books/claim/1', $data);

        $response->assertJson(['message' => 'Book claimed successfully']);

        $this->assertDatabaseHas('books', [
            'user_name' => 'Elon Musk',
            'user_email' => 'elonmusk@tesla.com',
            'claimed' => 1,
        ]);
    }

    public function test_claimBookFailure(): void 
    {
        Book::factory(1)->create();

        // edit a pre existing book from unclaimed to claimed 
        // then go and check if the book you claimed is claimed and that the user_name and user_email are in there
        //, and all other books are still unclaimed. 
        $data = [];
        $response = $this->putJson('api/books/claim/1', $data);

        $response->assertStatus(422)->assertJson(['message' => 'The name field is required. (and 1 more error)']);
    }


    public function test_returnBookSuccess(): void 
    {
        $book = Book::factory()->create();
        $book->claimed = 1;
        $book->save();

        // edit a pre existing book from unclaimed to claimed 
        // then go and check if the book you claimed is claimed and that the user_name and user_email are in there
        //, and all other books are still unclaimed. 
        $data = [
            'email' => 'elonmusk@tesla.com',
        ];
        $response = $this->putJson('api/books/return/1', $data);

        $response->assertJson(['message' => 'Book returned successfully']);

        $this->assertDatabaseHas('books', [
            'claimed' => 0,
            'id' => 1,
            'genre_id' => 1,
        ]);
    }

    public function test_returnBookFail(): void 
    {
        $book = Book::factory()->create();
        $book->claimed = 1;
        $book->save();

        // edit a pre existing book from unclaimed to claimed 
        // then go and check if the book you claimed is claimed and that the user_name and user_email are in there
        //, and all other books are still unclaimed. 
        $data = [
            'email' => 'elonmusk',
        ];
        $response = $this->putJson('api/books/return/1', $data);
        $response->assertInvalid('email');
        $response->assertStatus(422)->assertJson(['message' => 'The email field must be a valid email address.']);

        $this->assertDatabaseHas('books', [
            'claimed' => 1,
            'id' => 1,
            'genre_id' => 1,
        ]);
    }
}