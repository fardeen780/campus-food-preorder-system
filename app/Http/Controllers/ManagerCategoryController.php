<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FoodCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerCategoryController extends Controller
{
    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($ignoreId) {
                $query = FoodCategory::query()->whereRaw('lower(name) = ?', [mb_strtolower(trim((string) $value))]);

                if ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                }

                if ($query->exists()) {
                    $fail('A category with that name already exists.');
                }
            }],
            'sort_order' => ['required', 'integer', 'between:0,999'],
        ];
    }

    public function index(): View
    {
        return view('manager.categories', [
            'categories' => FoodCategory::withCount('foodItems')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['name'] = trim($data['name']);

        $category = FoodCategory::create($data);

        ActivityLog::record('category.created', $request->user(), $category, 'Category '.$category->name.' created');

        return back()->with('status', 'Category '.$category->name.' added.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $category = FoodCategory::findOrFail($id);
        $data = $request->validate($this->rules($id));
        $data['name'] = trim($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $category->update($data);

        ActivityLog::record('category.updated', $request->user(), $category, 'Category '.$category->name.' updated');

        return back()->with('status', 'Category '.$category->name.' saved.');
    }
}