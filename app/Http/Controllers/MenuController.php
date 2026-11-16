<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'outlet' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
        ]);

        $query = FoodItem::available()
            ->with(['outlet', 'category'])
            ->whereHas('outlet', fn ($q) => $q->where('is_active', true))
            ->whereHas('category', fn ($q) => $q->where('is_active', true));

        if (! empty($filters['q'])) {
            $term = addcslashes($filters['q'], '%_\\');
            $query->whereRaw('food_items.name ilike ?', ['%'.$term.'%']);
        }

        if (! empty($filters['outlet'])) {
            $query->where('food_items.outlet_id', $filters['outlet']);
        }

        if (! empty($filters['category'])) {
            $query->where('food_items.category_id', $filters['category']);
        }

        return view('student.menu', [
            'items' => $query->orderBy('food_items.name')->paginate(12)->withQueryString(),
            'outlets' => Outlet::where('is_active', true)->orderBy('name')->get(),
            'categories' => FoodCategory::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}