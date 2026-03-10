<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GenreManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Genre::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $genres = $query->orderBy('name', 'asc')->paginate(15);

        return view('admin.genres.index', compact('genres'));
    }

    public function create()
    {
        return view('admin.genres.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:genres,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'description']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        Genre::create($data);

        return redirect()->route('admin.genres.index')
            ->with('success', 'Genre created successfully.');
    }

    public function show(Genre $genre)
    {
        $genre->loadCount(['events', 'artists']);

        return view('admin.genres.show', compact('genre'));
    }

    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:genres,name,' . $genre->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'description']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;

        $genre->update($data);

        return redirect()->route('admin.genres.index')
            ->with('success', 'Genre updated successfully.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return redirect()->route('admin.genres.index')
            ->with('success', 'Genre deleted successfully.');
    }

    public function toggleStatus(Genre $genre)
    {
        $genre->update(['is_active' => !$genre->is_active]);

        return back()->with('success', 'Genre status updated successfully.');
    }
}










