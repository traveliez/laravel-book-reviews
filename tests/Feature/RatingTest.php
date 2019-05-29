<?php

namespace Tests\Feature;

use App\Book;
use App\Http\Resources\RatingResource;
use App\Rating;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RatingTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        factory(User::class, 50)
            ->create()
            ->each(function ($user) {
                $user->books()->save(factory(Book::class)->make());
            })
        ;

        $this->user = User::all()->random();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * @test
     */
    public function user_can_rating_the_book()
    {
        $this->actingAs($this->user);
        $book = Book::where('user_id', $this->user->id)->get()->random();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->json('POST', route('rating.books', $book->id), [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'rating' => rand(1, 5),
        ]);
        $getDataJson = $response->json('data');
        $ratingResource = new RatingResource(Rating::where('book_id', $book->id)->first());
        $resourceResponse = $ratingResource->response()->getData(true)['data'];
        $this->assertEquals($resourceResponse, $getDataJson);
    }
}