@extends('layouts.admin')

@section('title', 'Create Poll - Admin Panel')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Poll</h1>

    @include('admin.polls._form', [
        'action' => route('admin.polls.store'),
        'method' => 'POST',
        'poll' => null,
        'contexts' => $contexts,
    ])
</div>
@endsection
