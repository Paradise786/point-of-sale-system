<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $units = Unit::with('baseUnit')
            ->withCount('products')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('short_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('units.index', compact('units', 'search'));
    }

    public function create(): View
    {
        $baseUnits = Unit::whereNull('base_unit_id')->orderBy('name')->get();

        return view('units.create', compact('baseUnits'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
            'short_code' => ['required', 'string', 'max:20', 'unique:units,short_code'],
            'base_unit_id' => ['nullable', 'exists:units,id'],
            'operator' => ['required', 'in:*,/'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit): View
    {
        $baseUnits = Unit::whereNull('base_unit_id')
            ->where('id', '!=', $unit->id)
            ->orderBy('name')
            ->get();

        return view('units.edit', compact('unit', 'baseUnits'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name,'.$unit->id],
            'short_code' => ['required', 'string', 'max:20', 'unique:units,short_code,'.$unit->id],
            'base_unit_id' => ['nullable', 'exists:units,id'],
            'operator' => ['required', 'in:*,/'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Cannot delete unit assigned to products.');
        }

        if ($unit->subUnits()->count() > 0) {
            return back()->with('error', 'Cannot delete unit because other units depend on it as a base unit.');
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}
