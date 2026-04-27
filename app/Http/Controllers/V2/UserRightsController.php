<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UserRightsController extends Controller
{
    public function index()
    {
        return view('v2.users.index', [
            'users' => User::orderBy('name')->get(),
            'permissions' => Permission::where('name', 'like', 'v2 %')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $current = $user->permissions()->where('name', 'not like', 'v2 %')->pluck('name')->all();
        $user->syncPermissions(array_merge($current, $data['permissions'] ?? []));

        return back()->with('success', 'User rights updated.');
    }
}
