<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceCatalog;
use Illuminate\Http\Request;

class DeviceCatalogController extends Controller
{
    public function index()
    {
        $devices = DeviceCatalog::orderBy('sort_order')->orderBy('type')->orderBy('label')->get();
        return view('admin.device-catalog.index', compact('devices'));
    }

    public function data()
    {
        $devices = DeviceCatalog::where('is_active', true)
            ->orderBy('sort_order')->orderBy('type')->orderBy('label')
            ->get(['label', 'type', 'keywords']);

        return response()->json($devices->map(fn($d) => [
            'label' => $d->label,
            'type'  => $d->type,
            'q'     => $d->keywords ?? '',
        ]));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'    => 'required|string|max:255',
            'type'     => 'required|string|max:50',
            'keywords' => 'nullable|string',
        ]);

        $data['sort_order'] = DeviceCatalog::max('sort_order') + 1;
        $data['is_active']  = true;

        $device = DeviceCatalog::create($data);

        return response()->json(['ok' => true, 'device' => $device]);
    }

    public function update(Request $request, DeviceCatalog $device)
    {
        $data = $request->validate([
            'label'     => 'required|string|max:255',
            'type'      => 'required|string|max:50',
            'keywords'  => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $device->update($data);

        return response()->json(['ok' => true]);
    }

    public function destroy(DeviceCatalog $device)
    {
        $device->delete();
        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        foreach ($ids as $i => $id) {
            DeviceCatalog::where('id', $id)->update(['sort_order' => $i]);
        }
        return response()->json(['ok' => true]);
    }
}
