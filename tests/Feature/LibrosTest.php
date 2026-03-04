<?php

use App\Models\Book;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Book::query()->delete();
});

function autenticarComo(string $rol = User::ROLE_BIBLIOTECARIO): User
{
    $user = User::factory()->create();
    $user->syncRoles([$rol]);

    Sanctum::actingAs($user);

    return $user;
}

test('listar libros requiere autenticacion', function () {
    $response = $this->getJson('/api/v1/books');

    $response->assertUnauthorized();
});

test('puede listar libros', function () {
    autenticarComo(User::ROLE_DOCENTE);
    Book::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/books');

    $response->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'title', 'description', 'ISBN', 'total_copies', 'available_copies', 'is_available'],
        ]);

    expect($response->json())->toHaveCount(3);
});

test('puede ver detalle de un libro', function () {
    autenticarComo(User::ROLE_ESTUDIANTE);
    $book = Book::factory()->create();

    $response = $this->getJson("/api/v1/books/{$book->id}");

    $response->assertOk()
        ->assertJsonPath('id', $book->id)
        ->assertJsonPath('title', $book->title)
        ->assertJsonPath('ISBN', $book->ISBN);
});

test('bibliotecario puede crear libro', function () {
    autenticarComo(User::ROLE_BIBLIOTECARIO);

    $payload = [
        'title' => 'Clean Code',
        'description' => 'Libro de buenas practicas de desarrollo.',
        'ISBN' => '9780132350884',
        'total_copies' => 5,
        'available_copies' => 3,
    ];

    $response = $this->postJson('/api/v1/books', $payload);

    $response->assertCreated()
        ->assertJsonPath('title', $payload['title'])
        ->assertJsonPath('ISBN', $payload['ISBN'])
        ->assertJsonPath('is_available', 'Disponible');

    $this->assertDatabaseHas('books', [
        'title' => $payload['title'],
        'ISBN' => $payload['ISBN'],
        'total_copies' => 5,
        'available_copies' => 3,
        'is_available' => 1,
    ]);
});

test('crear libro falla con datos invalidos', function () {
    autenticarComo(User::ROLE_BIBLIOTECARIO);

    $response = $this->postJson('/api/v1/books', [
        'title' => '',
        'description' => 'Descripcion',
        'ISBN' => '9780132350884',
        'total_copies' => 2,
        'available_copies' => 3,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'available_copies']);
});

test('usuario sin permiso no puede crear libro', function () {
    autenticarComo(User::ROLE_ESTUDIANTE);

    $response = $this->postJson('/api/v1/books', [
        'title' => 'Nuevo libro',
        'description' => 'Descripcion',
        'ISBN' => '9780132350885',
        'total_copies' => 2,
        'available_copies' => 2,
    ]);

    $response->assertForbidden();
});

test('bibliotecario puede actualizar libro', function () {
    autenticarComo(User::ROLE_BIBLIOTECARIO);
    $book = Book::factory()->create([
        'title' => 'Titulo original',
        'ISBN' => '9780132350886',
        'total_copies' => 4,
        'available_copies' => 2,
    ]);

    $payload = [
        'title' => 'Titulo actualizado',
        'description' => 'Descripcion actualizada',
        'ISBN' => '9780132350887',
        'total_copies' => 8,
        'available_copies' => 8,
    ];

    $response = $this->putJson("/api/v1/books/{$book->id}", $payload);

    $response->assertOk()
        ->assertJsonPath('title', $payload['title'])
        ->assertJsonPath('ISBN', $payload['ISBN'])
        ->assertJsonPath('is_available', 'Disponible');

    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => $payload['title'],
        'ISBN' => $payload['ISBN'],
        'total_copies' => 8,
        'available_copies' => 8,
    ]);
});

test('bibliotecario puede eliminar libro', function () {
    autenticarComo(User::ROLE_BIBLIOTECARIO);
    $book = Book::factory()->create();

    $response = $this->deleteJson("/api/v1/books/{$book->id}");

    $response->assertOk()
        ->assertJsonPath('message', 'Book deleted successfully');

    $this->assertDatabaseMissing('books', ['id' => $book->id]);
});
