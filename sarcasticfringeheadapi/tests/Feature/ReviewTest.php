<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\Book;

class ReviewTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
    public function test_AddReviewSuccess(): void
    {

        Book::factory()->create();

        $book_data = [
            'name'=> 'Harry Pozza',
            'rating' => 4,
            'review' => 'Book was good',
            'book_id' => 1,
            
        ];
        
        $response = $this->postJson('/api/reviews', $book_data);
        $response->assertStatus(201)->assertJson(['message' => 'Review created']);

        $this->assertDatabaseHas('reviews', [
            'name'=> 'Harry Pozza',
            'rating' => 4,
            'review' => 'Book was good',
            'book_id' => 1,
        ]);
    }

    public function test_AddReviewFailure(): void
    {
        $book_data = [];
        $response = $this->postJson('/api/reviews', $book_data);
        $response->assertInvalid(['name', 'rating', 'review', 'book_id']);
    }

    public function test_AddReviewNoSuchbook(): void
    {
        Book::factory()->create();
        $book_data = [
            'name'=> 'Harry Pozza',
            'rating' => 4,
            'review' => 'Book was good',
            'book_id' => 2,
        ];
        $response = $this->postJson('/api/reviews', $book_data);
        
        $response->assertStatus(422)->assertJson(function(AssertableJson $json){
            $json->hasAll(['message', 'errors'])->has('errors', 1);
        });
    }


public function test_GetAllBookSearchWithGenreSuccess(): void
    {
        Book::factory(5)->create();
        $book = Book::factory()->create();
        $book->title = 'snozz';
        $book->save();

        $response = $this->getJson('/api/books?search=snozz&genre=6');
        $response->assertJson(function(AssertableJson $json){
            $json->hasAll(['message', 'data'])->has('data', 1);
        });
    }
}