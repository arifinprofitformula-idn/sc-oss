<?php

declare(strict_types=1);

namespace App\Http\Controllers\Silverchannel;

use App\Http\Controllers\Controller;
use App\Models\ReferralFollowUp;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('SILVERCHANNEL')) {
            abort(403);
        }

        $query = $this->buildQuery($request, $user);

        $sort = $request->string('sort', 'created_at')->toString();
        $direction = $request->string('direction', 'desc')->toString();

        if (!in_array($sort, ['name', 'email', 'city_name', 'status', 'created_at'], true)) {
            $sort = 'created_at';
        }

        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        // Validate per_page to prevent abuse or errors
        $perPage = $request->integer('per_page', 10);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        /** @var LengthAwarePaginator $prospects */
        $prospects = $query->orderBy($sort, $direction)
            ->paginate($perPage)
            ->appends($request->all());

        $totalDueToday = ReferralFollowUp::where('referrer_id', $user->id)
            ->whereNotNull('next_follow_up_at')
            ->whereDate('next_follow_up_at', '<=', now())
            ->count();

        return view('silverchannel.referrals.index', [
            'prospects' => $prospects,
            'filters' => [
                'status' => $request->string('status')->toString(),
                'from_date' => $request->date('from_date') ? $request->date('from_date')->format('Y-m-d') : null,
                'to_date' => $request->date('to_date') ? $request->date('to_date')->format('Y-m-d') : null,
                'search' => $request->string('search')->toString(),
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
            'totalDueToday' => $totalDueToday,
        ]);
    }

    private function buildQuery(Request $request, User $user): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::query()
            ->where('referrer_id', $user->id)
            ->with(['referralFollowUpAsReferred' => function ($q) use ($user): void {
                $q->where('referrer_id', $user->id);
            }]);

        $status = $request->string('status')->toString();
        $from = $request->date('from_date');
        $to = $request->date('to_date');
        $search = $request->string('search')->toString();

        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('whatsapp', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    public function updateFollowUp(Request $request, int $referredUserId): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('SILVERCHANNEL')) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string|max:50',
            'last_follow_up_at' => 'nullable|date',
            'next_follow_up_at' => 'nullable|date|after_or_equal:now',
            'note' => 'nullable|string',
        ]);

        $prospect = User::where('id', $referredUserId)
            ->where('referrer_id', $user->id)
            ->firstOrFail();

        $followUp = ReferralFollowUp::firstOrNew([
            'referrer_id' => $user->id,
            'referred_user_id' => $prospect->id,
        ]);

        $followUp->status = $validated['status'];
        $followUp->last_follow_up_at = $validated['last_follow_up_at'] ?? now();
        $followUp->next_follow_up_at = $validated['next_follow_up_at'] ?? null;
        $followUp->note = $validated['note'] ?? null;
        $followUp->save();

        return redirect()
            ->route('silverchannel.referrals.index', $request->except(['_token']))
            ->with('success', 'Status dan follow up berhasil diperbarui.');
    }

    public function export(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('SILVERCHANNEL')) {
            abort(403);
        }

        $query = $this->buildQuery($request, $user);
        
        // Ensure deterministic order for chunking
        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        $filename = 'my-referrals-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Lengkap', 'Email', 'Whatsapp', 'Asal Kota', 'Status', 'Last Follow Up', 'Next Follow Up']);

            // Use chunking to avoid memory issues with large datasets
            $query->chunk(200, function ($prospects) use ($handle) {
                foreach ($prospects as $prospect) {
                    /** @var User $prospect */
                    // Use correct relationship: follow-up made ON this prospect (by me)
                    $followUp = $prospect->referralFollowUpAsReferred;

                    fputcsv($handle, [
                        $prospect->name,
                        $prospect->email,
                        $prospect->whatsapp,
                        $prospect->city_name,
                        $followUp->status ?? 'PENDING',
                        optional($followUp->last_follow_up_at)->format('Y-m-d H:i') ?? '',
                        optional($followUp->next_follow_up_at)->format('Y-m-d H:i') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}

