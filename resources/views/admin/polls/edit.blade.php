@extends('layouts.admin')

@section('title', 'Edit Poll - Admin Panel')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Poll</h1>

    @include('admin.polls._form', [
        'action' => route('admin.polls.update', $poll),
        'method' => 'PUT',
        'poll' => $poll,
        'contexts' => $contexts,
    ])
</div>
@endsection
