<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\UserBannedNotification;
use App\Notifications\UserUnbannedNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display users list with filtering.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Filters
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->has('ban_type')) {
            $query->where('ban_type', $request->input('ban_type'));
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $query->latest()->paginate(50),
            'stats' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'banned' => User::where('ban_type', '!=', 'none')->count(),
            ],
            'filters' => $request->only(['search', 'role', 'ban_type']),
        ]);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,user,affiliate',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        // Log the action
        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'user_role_changed',
            'target_type' => 'user',
            'target_id' => $user->id,
            'metadata' => [
                'old_role' => $oldRole,
                'new_role' => $validated['role'],
            ],
        ]);

        return redirect()->back()->with('success', "User role updated to {$validated['role']}.");
    }

    /**
     * Ban a user.
     */
    public function ban(Request $request, User $user)
    {
        $validated = $request->validate([
            'ban_type' => 'required|in:soft,hard',
            'ban_reason' => 'required|string|max:1000',
            'ban_duration_days' => 'nullable|integer|min:1',
        ]);

        // Can't ban other admins
        if ($user->isAdmin() && $user->id !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'Cannot ban other admins.']);
        }

        $banData = [
            'ban_type' => $validated['ban_type'],
            'ban_reason' => $validated['ban_reason'],
            'banned_at' => now(),
            'banned_by' => auth()->id(),
        ];

        // Set expiration for soft bans
        if ($validated['ban_type'] === 'soft' && !empty($validated['ban_duration_days'])) {
            $banData['ban_expires_at'] = now()->addDays($validated['ban_duration_days']);
        }

        $user->update($banData);

        // Log the action
        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'user_banned',
            'target_type' => 'user',
            'target_id' => $user->id,
            'metadata' => [
                'ban_type' => $validated['ban_type'],
                'reason' => $validated['ban_reason'],
                'expires_at' => $banData['ban_expires_at'] ?? null,
            ],
        ]);

        // Send notification
        $user->notify(new UserBannedNotification($validated['ban_type'], $validated['ban_reason'], $banData['ban_expires_at'] ?? null));

        // If hard ban, also deactivate all their links
        if ($validated['ban_type'] === 'hard') {
            $user->links()->update(['is_active' => false]);
        }

        return redirect()->back()->with('success', 'User has been banned.');
    }

    /**
     * Unban a user.
     */
    public function unban(User $user)
    {
        $wasBanned = $user->ban_type !== 'none';

        $user->update([
            'ban_type' => 'none',
            'ban_reason' => null,
            'banned_at' => null,
            'ban_expires_at' => null,
            'banned_by' => null,
        ]);

        if ($wasBanned) {
            ActivityLog::create([
                'actor_id' => auth()->id(),
                'action' => 'user_unbanned',
                'target_type' => 'user',
                'target_id' => $user->id,
            ]);

            // Story 8.3: Notify user their ban has been lifted
            $user->notify(new UserUnbannedNotification());
        }

        return redirect()->back()->with('success', 'User has been unbanned.');
    }

    /**
     * Delete a user (GDPR right to be forgotten).
     */
    public function destroy(User $user)
    {
        // Don't allow self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete your own account from here.']);
        }

        // Anonymize links instead of deleting
        $user->links()->update(['user_id' => null]);

        // Log before deletion
        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'user_deleted',
            'target_type' => 'user',
            'target_id' => $user->id,
            'metadata' => [
                'email_hash' => hash('sha256', $user->email),
            ],
        ]);

        $user->delete();

        return redirect()->back()->with('success', 'User account has been deleted.');
    }
}
