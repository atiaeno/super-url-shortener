<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Payout;
use App\Models\PayoutAuditLog;
use App\Notifications\PayoutStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PayoutController extends Controller
{
    /**
     * Pending payout queue.
     */
    public function index(Request $request): Response
    {
        $status = $request->get('status', 'pending');

        $query = Payout::with(['affiliate.user:id,name,email', 'affiliate.tier:id,name'])
            ->latest();

        if (in_array($status, ['pending', 'approved', 'paid', 'rejected'])) {
            $query->where('status', $status);
        }

        $payouts = $query->paginate(20)->withQueryString();

        $stats = [
            'pending' => Payout::where('status', Payout::STATUS_PENDING)->count(),
            'approved' => Payout::where('status', Payout::STATUS_APPROVED)->count(),
            'paid' => Payout::where('status', Payout::STATUS_PAID)->count(),
            'rejected' => Payout::where('status', Payout::STATUS_REJECTED)->count(),
            'totalAmount' => Payout::where('status', Payout::STATUS_PENDING)->sum('amount'),
        ];

        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $payouts,
            'stats' => $stats,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Approve a payout.
     */
    public function approve(Request $request, Payout $payout): RedirectResponse
    {
        abort_unless($payout->isPending(), 422, 'Payout is not in pending state.');

        $oldStatus = $payout->status;
        $payout->update([
            'status' => Payout::STATUS_APPROVED,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $this->logAudit($payout, $oldStatus, Payout::STATUS_APPROVED, $request->note);

        // Notify affiliate of approval
        $payout->affiliate->user->notify(new PayoutStatusNotification($payout));

        return back()->with('success', 'Payout approved.');
    }

    /**
     * Reject a payout.
     */
    public function reject(Request $request, Payout $payout): RedirectResponse
    {
        abort_unless($payout->isPending(), 422, 'Payout is not in pending state.');

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        $oldStatus = $payout->status;
        $payout->update([
            'status' => Payout::STATUS_REJECTED,
            'admin_note' => $validated['note'],
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $this->logAudit($payout, $oldStatus, Payout::STATUS_REJECTED, $validated['note']);

        // Notify affiliate of rejection with reason
        $payout->affiliate->user->notify(new PayoutStatusNotification($payout, $validated['note']));

        return back()->with('success', 'Payout rejected.');
    }

    /**
     * Mark a payout as paid.
     */
    public function markPaid(Request $request, Payout $payout): RedirectResponse
    {
        abort_unless($payout->isApproved(), 422, 'Payout must be approved before marking paid.');

        $oldStatus = $payout->status;
        $payout->update([
            'status' => Payout::STATUS_PAID,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $affiliate = $payout->affiliate;

        // Since we now deduct both pending_earnings and referral_pending_earnings
        // when payout is requested, we just need to increment paid_earnings
        // Note: We don't track referral_paid_earnings separately in the payout amount
        $affiliate->increment('paid_earnings', $payout->amount);

        $this->logAudit($payout, $oldStatus, Payout::STATUS_PAID, $request->note);

        // Notify affiliate that payment has been sent
        $payout->affiliate->user->notify(new PayoutStatusNotification($payout));

        return back()->with('success', 'Payout marked as paid.');
    }

    /**
     * View audit log for a payout.
     */
    public function auditLog(Payout $payout): Response
    {
        $logs = $payout
            ->auditLogs()
            ->with('actor:id,name')
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Admin/Payouts/AuditLog', [
            'payout' => $payout->load('affiliate.user:id,name,email'),
            'logs' => $logs,
        ]);
    }

    /**
     * Append-only audit log entry.
     */
    private function logAudit(Payout $payout, string $oldStatus, string $newStatus, ?string $note): void
    {
        PayoutAuditLog::create([
            'payout_id' => $payout->id,
            'actor_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
            'created_at' => now(),
        ]);
    }
}
