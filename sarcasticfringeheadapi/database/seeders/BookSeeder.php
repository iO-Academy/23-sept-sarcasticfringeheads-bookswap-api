<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Genre;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the books table to remove any existing data
        Book::truncate();
        Genre::truncate();
        //create 3 genres;
        
            $genres = ['Sci-fi', 'Romance', 'Crime'];
            foreach($genres as $genre)
            {
                $entry = new Genre();
                $entry->name = $genre;
                $entry->save();
            }
        // Create 10 books with random genre assignments
        for ($i = 0; $i < 10; $i++) {
            $genreId = rand(1, Genre::count());
            $book = new Book();
            $book->title = 'Book Title ' . $i;
            $book->author = 'Author ' . $i;
            $book->genre_id = $genreId;
            $book->save();
        }
    }
}
