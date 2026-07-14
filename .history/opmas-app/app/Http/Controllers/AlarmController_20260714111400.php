<?php
namespace App\Http\Controllers;

use App\Models\Alarm;

class AlarmController extends Controller
{
    public function index()
    {
        $alarms = Alarm::orderByRaw("CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'WARNING' THEN 2 WHEN 'INFO' THEN 3 ELSE 4 END")
                       ->orderBy('created_at', 'desc')
                       ->paginate(20);

        return view('alarms.index', compact('alarms'));
    }
}