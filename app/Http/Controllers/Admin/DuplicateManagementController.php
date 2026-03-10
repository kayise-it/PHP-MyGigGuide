<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DuplicateNameService;
use Illuminate\Http\Request;

class DuplicateManagementController extends Controller
{
    protected DuplicateNameService $duplicateService;

    public function __construct(DuplicateNameService $duplicateService)
    {
        $this->duplicateService = $duplicateService;
    }

    /**
     * Display the duplicate names management page.
     */
    public function index(Request $request)
    {
        $entityType = $request->get('type');
        
        if ($entityType) {
            // Show duplicates for specific entity type
            $duplicates = [
                $entityType => [
                    'label' => $this->duplicateService->getEntityTypes()[$entityType]['value'] ?? ucfirst($entityType),
                    'groups' => $this->duplicateService->findDuplicatesForEntity($entityType),
                ]
            ];
            $duplicates[$entityType]['total_duplicates'] = collect($duplicates[$entityType]['groups'])
                ->sum(fn($group) => count($group['items']));
        } else {
            // Show all duplicates
            $duplicates = $this->duplicateService->findAllDuplicates();
        }

        $summary = $this->duplicateService->getDuplicateSummary();
        $entityTypes = $this->duplicateService->getEntityTypes();

        return view('admin.duplicates.index', compact('duplicates', 'summary', 'entityTypes', 'entityType'));
    }

    /**
     * Get details for a specific entity.
     */
    public function show(Request $request, string $type, int $id)
    {
        $details = $this->duplicateService->getEntityDetails($type, $id);

        if (!$details) {
            abort(404, 'Entity not found');
        }

        if ($request->wantsJson()) {
            return response()->json($details);
        }

        return view('admin.duplicates.show', compact('details', 'type'));
    }

    /**
     * Rename an entity to resolve a duplicate.
     */
    public function rename(Request $request, string $type, int $id)
    {
        $request->validate([
            'new_name' => 'required|string|max:255',
        ]);

        try {
            $this->duplicateService->renameEntity($type, $id, $request->new_name);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Entity renamed successfully.']);
            }

            return redirect()->route('admin.duplicates.index', ['type' => $type])
                ->with('success', 'Entity renamed successfully.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Get summary stats via AJAX.
     */
    public function summary()
    {
        return response()->json($this->duplicateService->getDuplicateSummary());
    }
}


