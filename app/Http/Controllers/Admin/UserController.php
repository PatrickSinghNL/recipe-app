<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/users/Index', [
            'users' => User::latest()->get(),
        ]);
    }

    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);

        return back()->with('success', "User {$user->name} approved.");
    }

    public function toggleAdmin(User $user)
    {
        // Don't let user toggle themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->update(['is_admin' => ! $user->is_admin]);

        return back()->with('success', "User {$user->name} admin status updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return back()->with('success', "User {$user->name} deleted.");
    }
}
