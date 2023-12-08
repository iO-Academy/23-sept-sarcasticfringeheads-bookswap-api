<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use Database\Factories\GenreFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;

class BookTest extends TestCase
{
    use DatabaseMigrations;

    public function test_example(): void
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
}
