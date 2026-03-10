<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class CapabilityManagementController extends Controller
{
    /**
     * Simple, grouped capability matrix for roles.
     */
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get()->keyBy('name');

        $configGroups = config('capabilities.groups', []);

        $groups = [];

        // Build groups based on config
        foreach ($configGroups as $groupKey => $capabilityNames) {
            $groupCaps = [];

            foreach ($capabilityNames as $name) {
                if (! $permissions->has($name)) {
                    continue;
                }

                $groupCaps[] = $permissions->get($name);
            }

            if (! empty($groupCaps)) {
                $groups[$groupKey] = $groupCaps;
            }
        }

        // Find any permissions not referenced in groups
        $groupedNames = collect($configGroups)->flatten()->all();
        $ungrouped = $permissions->filter(function ($perm) use ($groupedNames) {
            return ! in_array($perm->name, $groupedNames, true);
        });

        if ($ungrouped->isNotEmpty()) {
            $groups['other'] = $ungrouped->values();
        }

        return view('admin.capabilities.index', [
            'roles' => $roles,
            'groups' => $groups,
        ]);
    }

    /**
     * Update role ↔ capability assignments.
     */
    public function update(Request $request)
    {
        $matrix = $request->input('matrix', []);

        $allPermissions = Permission::pluck('name', 'name');

        foreach (Role::all() as $role) {
            $selected = array_keys($matrix[$role->id] ?? []);

            // Only keep valid permissions
            $selected = array_values(array_intersect($selected, $allPermissions->keys()->all()));

            $role->syncPermissions($selected);
        }

        return redirect()
            ->route('admin.capabilities.index')
            ->with('success', 'Capabilities updated successfully.');
    }
}

