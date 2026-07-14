<?php
namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::orderBy('next_service')->get();
        $statusCounts = Equipment::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $upcomingService = Equipment::whereDate('next_service', '<=', now()->addDays(14))->orderBy('next_service')->get();

        return view('equipment.index', compact('equipment', 'statusCounts', 'upcomingService'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'         => 'required|string|max:20|unique:equipment',
            'name'         => 'required|string|max:255',
            'status'       => ['required', Rule::in(['ONLINE', 'OFFLINE', 'FAULT', 'MAINTENANCE'])],
            'last_service' => 'nullable|date',
            'next_service' => 'nullable|date',
            'notes'        => 'nullable|string',
        ]);

        Equipment::create($data);

        return redirect()->route('equipment')->with('status', 'Equipment created successfully.');
    }

    public function update(Request $request, Equipment $equipment)
    {
        $user = auth()->user();

        if ($user->isSystemAdmin()) {
            $data = $request->validate([
                'code'         => ['required', 'string', 'max:20', Rule::unique('equipment')->ignore($equipment->id)],
                'name'         => 'required|string|max:255',
                'status'       => ['required', Rule::in(['ONLINE', 'OFFLINE', 'FAULT', 'MAINTENANCE'])],
                'last_service' => 'nullable|date',
                'next_service' => 'nullable|date',
                'notes'        => 'nullable|string',
            ]);
        } else {
            // Admin role: can only update status, last_service, next_service, notes
            $data = $request->validate([
                'status'       => ['required', Rule::in(['ONLINE', 'OFFLINE', 'FAULT', 'MAINTENANCE'])],
                'last_service' => 'nullable|date',
                'next_service' => 'nullable|date',
                'notes'        => 'nullable|string',
            ]);
        }

        $equipment->update($data);

        return redirect()->route('equipment')->with('status', 'Equipment updated successfully.');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return redirect()->route('equipment')->with('status', 'Equipment record deleted successfully.');
    }
}