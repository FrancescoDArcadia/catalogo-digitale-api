<?php
use App\Models\Category;
use App\Models\User;

test('un visitatore anonimo non può ottenere la lista delle categorie', function () {
    $response = $this->getJson('/api/categories');

    $response->assertStatus(401);
});

test('un utente autenticato può vedere la lista delle categorie', function() {
    $user= User::factory()->create();
    Category::factory()->count(3)->create();
    $response = $this->actingAs($user, 'sanctum')->getJson('/api/categories');
    $response->assertStatus(200)->assertJsonCount(3, 'data');
});

test('un admin può creare una categoria', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $response = $this->actingAs($admin, 'sanctum')->postJson('/api/categories', [
        'name' => 'Fantascienza',
        'slug' => 'fantascienza',
    ]);
    $response->assertStatus(201)->assertJsonPath('data.name', 'Fantascienza');

    $this->assertDatabaseHas('categories', [
        'name' => 'Fantascienza',
        'slug' => 'fantascienza',
    ]);
});

test('un visitatore non può creare una categoria', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/categories', [
        'name' => 'Fantascienza',
        'slug' => 'fantascienza',
    ]);
    
    $response->assertStatus(403);
});

test('creare una categoria senza i campi obbligatori fallisce la validazione', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $response = $this->actingAs($admin, 'sanctum')->postJson('api/categories', []);
    
    $response->assertStatus(422)->assertJsonValidationErrors(['name', 'slug']);
});