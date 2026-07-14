<?php
namespace App\Http\Controllers;

use App\Models\Alarm;
use Illuminate\Http\Request;

class AlarmController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');
        $query = Alarm::query();

        if ($status === 'resolved') {
            $query->resolved();
        } elseif ($status !== 'all') {
            $query->active();
        }

        $alarms = $query->orderByRaw("CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'WARNING' THEN 2 WHEN 'INFO' THEN 3 ELSE 4 END")
                        ->orderBy('created_at', 'desc')
                        ->paginate(20)
                        ->withQueryString();

        $counts = Alarm::selectRaw('resolved, count(*) as total')
                       ->groupBy('resolved')
                       ->pluck('total', 'resolved');

        return view('alarms.index', compact('alarms', 'status', 'counts'));
    }

    public function resolve(Alarm $alarm)
    {
        $alarm->update([
            'resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('status', 'Alarm marked as resolved.');
    }

    public function destroy(Alarm $alarm)
    {
        $alarm->delete();
        return back()->with('status', 'Alarm record deleted successfully.');
    }
}