<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

// Phase: category administration. Managers can maintain the controlled vocabulary
// used by products; categories are archived instead of physically deleted.
class CategoryController extends Controller
{
    private function manager(Request $request): void
    {
        abort_unless($request->user()->isManager(), 403);
    }

    public function index(Request $request)
    {
        $this->manager($request);
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->manager($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        Category::create($data);
        return Redirect::route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $this->manager($request);
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);
        return Redirect::route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function archive(Request $request, $id)
    {
        $this->manager($request);
        $category = Category::findOrFail($id);
        $category->update(['is_active' => false]);
        return Redirect::route('categories.index')->with('success', 'Category archived successfully.');
    }
}
