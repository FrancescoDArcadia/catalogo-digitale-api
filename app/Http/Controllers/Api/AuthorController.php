<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AuthorHasWorkException;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuthorController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Author::class, "author");
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AuthorResource::collection(Author::withCount('works')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $author = Author::create($request->validated());

        return AuthorResource::make($author)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
        return AuthorResource::make($author->load('works.category')->loadCount('works'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author)
    {
        $author->update($request->validated());
        return AuthorResource::make($author);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        if ($author->works()->exists()) {
            throw new AuthorHasWorkException();
        }
        $author->delete();
        return response()->noContent();
    }
}
