<?php
namespace App\Http\Controllers;
use App\Models\Genre;

class GenreController extends Controller
{
    private Genre $genre;
    public function __construct(Genre $genre)
    {
        $this->genre = $genre;
    }

    private function serializeGenres($genres) {
        // make an empty array
        // make a foreach loop, where for each book you add to the array a new item
        // containing the name and id of the genre
        // return the array
        $output = [];
        foreach ($genres as $genre) {
        $output[] = [
            'id' => $genre->id,
            'name' => $genre->name,
            ];
        }
        return $output;
    }
    public function getAllGenres() {
        $genres = Genre::all();
        $serialized_genres = $this->serializeGenres($genres);

        return response()->json([
            'message' => "Genre's gotted",
            'data' => $serialized_genres
        ], 200);
    }
}
