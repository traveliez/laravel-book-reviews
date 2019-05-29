<?php

namespace Tests\Feature;

use App\Book;
use App\Http\Resources\BookResource;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
    public function show_list_books_with_ratings_and_pagination()
    {
        $getResource = BookResource::collection(Book::with('ratings')->paginate());

        $response = $this->json('GET', route('books.index'));

        $getDataJson = $response->json('data');
        $resourceResponse = $getResource->response()->getData(true)['data'];
        $response->assertJsonCount(count($getDataJson), ['data'])
            ->assertStatus(200)
        ;
        $this->assertEquals($resourceResponse, $getDataJson);
    }

    /**
     * @test
     */
    public function authenticated_user_can_add_new_book()
    {
        $this->actingAs($this->user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->json('POST', route('books.store'), [
            'title' => 'Book Title',
            'description' => 'this is description',
        ]);
        $newResource = new BookResource(Book::find($response->json('data')['id']));
        $resourceResponse = $newResource->response()->getData(true)['data'];
        $getDataJson = $response->json('data');
        $response->assertStatus(201);

        $this->assertEquals($resourceResponse, $getDataJson);
    }

    /**
     * @test
     */
    public function authenticated_user_cant_add_new_book_without_title()
    {
        $this->actingAs($this->user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->json('POST', route('books.store'), [
            'title' => '',
            'description' => 'this is description',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title')
        ;
    }

    /**
     * @test
     */
    public function authenticated_user_can_update_their_book()
    {
        $this->actingAs($this->user);
        $book = Book::where('user_id', $this->user->id)->get()->random();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->json('PUT', route('books.update', $book->id), [
            'title' => 'New Title',
            'description' => $this->faker->paragraph(),
        ]);

        $updateResource = new BookResource(Book::find($response->json('data')['id']));
        $resourceResponse = $updateResource->response()->getData(true)['data'];
        $getDataJson = $response->json('data');
        $response->assertStatus(200);

        $this->assertEquals($resourceResponse, $getDataJson);
    }

    /**
     * @test
     */
    public function authenticated_user_can_delete_their_book()
    {
        $this->actingAs($this->user);
        $book = Book::where('user_id', $this->user->id)->get()->random();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->json('DELETE', route('books.destroy', $book->id));

        $response->assertStatus(204);
    }
}
