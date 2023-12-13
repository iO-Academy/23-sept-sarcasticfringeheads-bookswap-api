<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
