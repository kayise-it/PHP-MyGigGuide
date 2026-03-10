@extends('layouts.admin')

@section('title', 'Role Capabilities - My Gig Guide')
@section('page-title', 'Role Capabilities')
@section('description', 'Configure which capabilities each role has, using a simple matrix.')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Role Capabilities</h2>
        <p class="text-sm text-gray-600">
            Tick the boxes to control what each role is allowed to do. Changes are saved instantly for all users with that role.
        </p>
    </div>

    <form action="{{ route('admin.capabilities.update') }}" method="POST" class="space-y-6">
        @csrf

        @foreach($groups as $groupKey => $permissions)
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">
                            {{ ucfirst(str_replace('_', ' ', $groupKey)) }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            {{ count($permissions) }} capabilities
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">
                                    Capability
                                </th>
                                @foreach($roles as $role)
                                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $role->display_name ?? ucfirst($role->name) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($permissions as $permission)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <div class="font-medium">
                                            {{ $permission->display_name ?? $permission->name }}
                                        </div>
                                        @if($permission->description)
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                {{ $permission->description }}
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                {{ $permission->name }}
                                            </div>
                                        @endif
                                    </td>

                                    @foreach($roles as $role)
                                        <td class="px-3 py-3 text-center align-middle">
                                            <input
                                                type="checkbox"
                                                name="matrix[{{ $role->id }}][{{ $permission->name }}]"
                                                value="1"
                                                class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                                {{ $role->hasPermission($permission->name) ? 'checked' : '' }}
                                            />
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

