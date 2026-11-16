<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CartItem;
use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\Outlet;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagerFoodController extends Controller
{
    private function rules(): array
    {
        return [
            'outlet_id' => ['required', Rule::exists('outlets', 'id')],
            'category_id' => ['required', Rule::exists('food_categories', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'regex:/^\d{1,8}(\.\d{1,2})?$/'],
            'icon' => ['nullable', 'string', 'max:16'],
        ];
    }

    private function formData(FoodItem $item): array
    {
        return [
            'item' => $item,
            'outlets' => Outlet::orderBy('name')->get(),
            'categories' => FoodCategory::orderBy('sort_order')->get(),
        ];
    }

    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'outlet' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['all', 'available', 'unavailable', 'removed'])],
        ]);
        $status = $f['status'] ?? 'all';

        $query = FoodItem::query()->with(['outlet', 'category']);

        if ($status === 'removed') {
            $query->onlyTrashed();
        } elseif ($status === 'available') {
            $query->where('is_available', true);
        } elseif ($status === 'unavailable') {
            $query->where('is_available', false);
        }

        if (! empty($f['q'])) {
            $query->whereRaw('food_items.name ilike ?', ['%'.addcslashes($f['q'], '%_\\').'%']);
        }

        if (! empty($f['outlet'])) {
            $query->where('outlet_id', $f['outlet']);
        }

        return view('manager.foods', [
            'items' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'outlets' => Outlet::orderBy('name')->get(),
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('manager.food-form', $this->formData(new FoodItem(['is_available' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['name'] = trim($data['name']);
        $data['is_available'] = $request->boolean('is_available');

        try {
            $item = FoodItem::create($data);
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors(['name' => 'This outlet already has an item with that name.']);
        }

        ActivityLog::record('food.created', $request->user(), $item, 'Food item '.$item->name.' created');

        return redirect()->route('manager.foods')->with('status', $item->name.' was added to the menu.');
    }

    public function edit(int $id): View
    {
        return view('manager.food-form', $this->formData(FoodItem::findOrFail($id)));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = FoodItem::findOrFail($id);
        $data = $request->validate($this->rules());
        $data['name'] = trim($data['name']);
        $data['is_available'] = $request->boolean('is_available');

        $oldPrice = $item->price;

        try {
            $item->update($data);
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors(['name' => 'This outlet already has an item with that name.']);
        }

        $props = ($oldPrice !== $item->price) ? ['old_price' => $oldPrice, 'new_price' => $item->price] : [];
        ActivityLog::record('food.updated', $request->user(), $item, 'Food item '.$item->name.' updated', $props);

        return redirect()->route('manager.foods')->with('status', $item->name.' was updated. New prices apply to new orders only.');
    }

    public function toggle(Request $request, int $id): RedirectResponse
    {
        $item = FoodItem::findOrFail($id);
        $item->update(['is_available' => ! $item->is_available]);

        ActivityLog::record('food.availability', $request->user(), $item,
            $item->name.' marked '.($item->is_available ? 'available' : 'unavailable'));

        return back()->with('status', $item->name.' is now '.($item->is_available ? 'available' : 'unavailable').'.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $item = FoodItem::findOrFail($id);
        $item->delete();
        CartItem::where('food_item_id', $item->id)->delete();

        ActivityLog::record('food.removed', $request->user(), $item, $item->name.' removed from the menu');

        return back()->with('status', $item->name.' was removed. Past orders keep their history.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $item = FoodItem::onlyTrashed()->findOrFail($id);

        try {
            $item->restore();
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', 'Another item with the same name already exists at that outlet.');
        }

        ActivityLog::record('food.restored', $request->user(), $item, $item->name.' restored');

        return back()->with('status', $item->name.' was restored.');
    }
}