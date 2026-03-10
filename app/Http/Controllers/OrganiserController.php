<?php

namespace App\Http\Controllers;

use App\Models\Organiser;
use Illuminate\Http\Request;

class OrganiserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organisers = Organiser::with(['user'])
            ->orderBy('organisation_name')
            ->paginate(12);

        return view('organisers.index', compact('organisers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organiser = Organiser::with(['user', 'events' => function ($query) {
            $query->where('date', '>=', now())->orderBy('date');
        }])->findOrFail($id);

        return view('organisers.show', compact('organiser'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
