<?php
namespace App\Http\Controllers;

use App\Models\Alarm;

class AlarmController extends Controller
{
    public function index()
    {
        $alarms = Alarm::orderByRaw("FIELD(severity,'CRITICAL','WARNING','INFO')")
                       ->orderBy('created_at', 'desc')
                       ->paginate(20);

        return view('alarms.index', compact('alarms'));
    }
}