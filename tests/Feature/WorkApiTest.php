<?php
use App\Models\Author;
use App\Models\Category;
use App\Models\User;
use App\Models\Work;

beforeEach(function(){
    $this->admin = User::factory()->create(['role'=> 'admin']);
});

test('admin può creare un work', function () {
    $author= Author::factory()->create();
    $category = Category::factory()->create();

    $response= $this->actingAs($this->admin, 'sanctum')->postJson('/api/works', [
        'title' => 'Il Nome della Rosa',
        'author_id' => $author->id,
        'category_id' => $category->id,
    ]);
    $response->assertStatus(201);

    $this->assertDatabaseHas('works', [
        'title' => 'Il Nome della Rosa',
        'author_id' => $author->id,
        'category_id' => $category->id,
    ]);
});


test('admin può eliminare un work', function () {
    $work= Work::factory()->create();

    $response= $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/works/{$work->id}");
    $response->assertStatus(204);
});