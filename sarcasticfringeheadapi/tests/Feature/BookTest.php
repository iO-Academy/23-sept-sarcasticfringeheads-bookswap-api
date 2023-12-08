<?php

namespace Tests\Feature;

use App\Models\Book;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;

class BookTest extends TestCase
{
    use DatabaseMigrations;

    public function test_example(): void
    {
        Book::factory()->create();

        $response = $this->getJson('/api/books');

        $response
            ->assertStatus(200)
            ->assertJson(function(AssertableJson $json){
                $json
                    ->hasAll(['message', 'data'])
                    ->has('data', 1, function(AssertableJson $json){
                        $json->hasAll(['id', 'title', 'author', 'image', 'genre_id', 'created_at', 'updated_at', 'claimed']);
                    //     ->whereAllType([
                    //         'id'=> 'integer',
                    //         'name'=> 'string',
                    //         'price'=> 'integer',
                    //         'image'=> 'string',
                    //         'description'=>'string',
                    //     ]);
                    });
            });

    }
}
