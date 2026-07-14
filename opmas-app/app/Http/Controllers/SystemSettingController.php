<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'pressure_low'     => 'required|numeric|min:0',
            'purity_low'       => 'required|numeric|min:0|max:100',
            'flow_rate_low'    => 'required|numeric|min:0',
            'temperature_high' => 'required|numeric|min:0',
            'tank_level_low'   => 'required|numeric|min:0|max:100',
            'simulation_mode'   => 'required|in:0,1',
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::setValue($key, $value);
        }

        return redirect()->route('settings.index')->with('status', 'System thresholds updated successfully.');
    }
}
