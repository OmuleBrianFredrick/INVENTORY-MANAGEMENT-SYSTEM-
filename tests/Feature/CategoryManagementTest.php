<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Phase: regression coverage for controlled category administration.
class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_category(): void
    {
        $manager = User::create([
            'name' => 'Inventory Manager',
            'email' => 'manager@example.test',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)->post(route('categories.store'), [
            'name' => 'Hardware',
            'description' => 'Hardware products',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Hardware', 'is_active' => true]);
    }

    public function test_staff_cannot_manage_categories(): void
    {
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.test',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->post(route('categories.store'), ['name' => 'Blocked']);

        $response->assertForbidden();
        $this->assertDatabaseMissing('categories', ['name' => 'Blocked']);
    }

    public function test_archiving_category_preserves_the_record(): void
    {
        $manager = User::create([
            'name' => 'Inventory Manager',
            'email' => 'manager2@example.test',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);
        $category = Category::create(['name' => 'Obsolete', 'is_active' => true]);

        $response = $this->actingAs($manager)->post(route('categories.archive', $category->id));

        $response->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
