<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ServiceTypeController extends Controller
{
    private function saveServiceImage(UploadedFile $file, int $serviceId): string
    {
        $ext      = $file->getClientOriginalExtension();
        $filename = $serviceId . '-' . time() . '.' . $ext;
        $dir      = public_path('images/services');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file->move($dir, $filename);

        return $filename;
    }

    private function deleteServiceImage(?string $filename): void
    {
        if (!$filename) return;

        // Don't delete the shipped placeholder SVGs
        if (str_ends_with($filename, '.svg') && !str_contains($filename, '-')) return;

        $path = public_path('images/services/' . $filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function index(Request $request)
    {
        $filter = $request->input('filter', 'active');
        $query  = ServiceType::orderBy('sort_order')->orderBy('name');

        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        }

        $services = $query->get();

        return view('admin.services.index', compact('services', 'filter'));
    }

    public function create()
    {
        $icons = ServiceType::iconSet();
        return view('admin.services.create', compact('icons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255|unique:service_types,name',
            'icon'               => 'nullable|string|max:50',
            'description'        => 'nullable|string',
            'default_unit_price' => 'nullable|numeric|min:0',
            'image'              => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $validIcons = array_keys(ServiceType::iconSet());
        $icon       = (!empty($data['icon']) && in_array($data['icon'], $validIcons)) ? $data['icon'] : null;

        $maxOrder = ServiceType::max('sort_order') ?? 0;

        $service = ServiceType::create([
            'name'               => $data['name'],
            'icon'               => $icon,
            'description'        => $data['description'] ?? null,
            'default_unit_price' => $data['default_unit_price'] ?? null,
            'sort_order'         => $maxOrder + 1,
            'is_active'          => true,
        ]);

        if ($request->hasFile('image')) {
            $service->image = $this->saveServiceImage($request->file('image'), $service->id);
            $service->save();
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Service "' . $data['name'] . '" added to catalog.');
    }

    public function edit(ServiceType $service)
    {
        $icons = ServiceType::iconSet();
        return view('admin.services.edit', compact('service', 'icons'));
    }

    public function update(Request $request, ServiceType $service)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255|unique:service_types,name,' . $service->id,
            'icon'               => 'nullable|string|max:50',
            'description'        => 'nullable|string',
            'default_unit_price' => 'nullable|numeric|min:0',
            'image'              => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $validIcons    = array_keys(ServiceType::iconSet());
        $service->icon = (!empty($data['icon']) && in_array($data['icon'], $validIcons)) ? $data['icon'] : null;

        $service->name               = $data['name'];
        $service->description        = $data['description'] ?? null;
        $service->default_unit_price = $data['default_unit_price'] ?? null;

        if ($request->boolean('remove_image') && $service->image) {
            $this->deleteServiceImage($service->image);
            $service->image = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteServiceImage($service->image);
            $service->image = $this->saveServiceImage($request->file('image'), $service->id);
        }

        $service->save();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:service_types,id']);

        foreach ($data['ids'] as $position => $id) {
            ServiceType::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function toggle(ServiceType $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        $label = $service->is_active ? 'activated' : 'inactivated';

        return back()->with('success', '"' . $service->name . '" ' . $label . '.');
    }
}
