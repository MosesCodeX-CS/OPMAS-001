<?php
namespace App\Http\Controllers;

use App\Models\Equipment;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::orderBy('next_service')->get();
        $statusCounts = Equipment::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $upcomingService = Equipment::whereDate('next_service', '<=', now()->addDays(14))->orderBy('next_service')->get();

        return view('equipment.index', compact('equipment', 'statusCounts', 'upcomingService'));
    }
}