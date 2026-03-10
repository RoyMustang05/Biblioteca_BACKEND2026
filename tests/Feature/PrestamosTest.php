
<?php

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Préstamos', function () {
    it('puede prestar un libro disponible', function () {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $book = Book::factory()->create(['available_copies' => 2, 'is_available' => true]);
        $payload = [
            'requester_name' => 'Juan',
            'book_id' => $book->id,
        ];
        $response = $this->postJson('/api/v1/loans', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('loans', ['book_id' => $book->id, 'requester_name' => 'Juan']);
        $book->refresh();
        expect($book->available_copies)->toBe(1);
    });

    it('no puede prestar un libro no disponible', function () {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $book = Book::factory()->create(['available_copies' => 0, 'is_available' => false]);
        $payload = [
            'requester_name' => 'Ana',
            'book_id' => $book->id,
        ];
        $response = $this->postJson('/api/v1/loans', $payload);
        $response->assertStatus(422);
        $response->assertJson(['message' => 'Book is not available']);
    });

    it('puede devolver un libro prestado', function () {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $book = Book::factory()->create(['available_copies' => 0, 'is_available' => false]);
        $loan = Loan::factory()->create(['book_id' => $book->id, 'return_at' => null, 'requester_name' => 'Juan']);
        $response = $this->postJson("/api/v1/loans/{$loan->id}/return");
        $response->assertStatus(200);
        $loan->refresh();
        expect($loan->return_at)->not->toBeNull();
        $book->refresh();
        expect($book->available_copies)->toBe(1);
        expect((bool)$book->is_available)->toBeTrue();
    });

    it('no puede devolver un préstamo ya devuelto', function () {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $book = Book::factory()->create(['available_copies' => 1, 'is_available' => true]);
        $loan = Loan::factory()->create(['book_id' => $book->id, 'return_at' => now(), 'requester_name' => 'Ana']);
        $response = $this->postJson("/api/v1/loans/{$loan->id}/return");
        $response->assertStatus(422);
        $response->assertJson(['message' => 'Loan already returned']);
    });

    it('puede consultar el historial de préstamos', function () {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $book = Book::factory()->create();
        Loan::factory()->count(3)->create(['book_id' => $book->id, 'requester_name' => 'Juan', 'user_id' => $user->id]);
        $response = $this->getJson('/api/v1/loans');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        expect($response->json('data'))->toHaveCount(3);
    });

    it('filtra préstamos por user_id específico', function () {
        $user1 = \App\Models\User::factory()->create();
        $user2 = \App\Models\User::factory()->create();
        $book = Book::factory()->create();
        Loan::factory()->count(2)->create(['book_id' => $book->id, 'user_id' => $user1->id]);
        Loan::factory()->count(1)->create(['book_id' => $book->id, 'user_id' => $user2->id]);
        $this->actingAs($user1);
        $response = $this->getJson('/api/v1/loans?user_id=' . $user1->id);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        expect($response->json('data'))->toHaveCount(2);
    });
});
