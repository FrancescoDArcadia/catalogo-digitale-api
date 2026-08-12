<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkRequest;
use App\Http\Requests\UpdateWorkRequest;
use App\Http\Resources\WorkResource;
use Illuminate\Http\Request;
use App\Models\Work;
use App\Policies\WorkPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct() 
    {
        $this->authorizeResource(Work::class, 'work');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //$works = Work::with(['author', 'category', 'tags'])->get();
        $works = Work::query()
        ->with(['author', 'category', 'tags'])
        ->filter($request->only(['search', 'category_id', 'author_id', 'tag_id', 'year_from', 'year_to']))
        ->sort($request->input('sort_by'),$request->input('sort_dir'))
        ->paginate(15);
        return WorkResource::collection($works);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkRequest $request)
    {
        $validated= $request->validated();
        $work = Work::create(collect($validated)->except('tags')->all());
        if (!empty($validated['tags'])) {
            $work->tags()->sync($validated['tags']);
        }
        $work->load(['author', 'category', 'tags']);
        return WorkResource::make($work)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Work $work)
    {
        return WorkResource::make($work->load(['author', 'category', 'tags']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkRequest $request, Work $work)
    {
        $validated = $request->validated();
        $work->update(collect($validated)->except('tags')->all());
        if (array_key_exists('tags', $validated)) {
            $work->tags()->sync($validated['tags']);
        }
        return WorkResource::make($work->load(['author','category','tags']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Work $work)
    {
        $work->delete();
        return response()->noContent();
    }

}
