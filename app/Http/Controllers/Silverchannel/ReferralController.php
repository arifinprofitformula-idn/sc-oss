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
        $user = Auth::user();

        if (!$user || !$user->hasRole('SILVERCHANNEL')) {
            abort(403);
        }

        $query = User::query()
            ->where('referrer_id', $user->id)
            ->with(['referralFollowUpAsReferred' => function ($q) use ($user): void {
                $q->where('referrer_id', $user->id);
            }]);

        $status = $request->get('status');
        $city = $request->get('city');
        $from = $request->get('from_date');
        $to = $request->get('to_date');
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if ($status && $status !== 'ALL') {
            $query->whereHas('referralFollowUpAsReferred', function ($q) use ($status, $user): void {
                $q->where('referrer_id', $user->id)->where('status', $status);
            });
        }

        if ($city) {
            $query->where('city_name', 'like', '%' . $city . '%');
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

        if (!in_array($sort, ['name', 'email', 'city_name', 'created_at'], true)) {
            $sort = 'created_at';
        }

        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        /** @var LengthAwarePaginator $prospects */
        $prospects = $query->orderBy($sort, $direction)
            ->paginate((int) $request->get('per_page', 10))
            ->appends($request->all());

        $totalDueToday = ReferralFollowUp::where('referrer_id', $user->id)
            ->whereNotNull('next_follow_up_at')
            ->whereDate('next_follow_up_at', '<=', now())
            ->count();

        return view('silverchannel.referrals.index', [
            'prospects' => $prospects,
            'filters' => [
                'status' => $status,
                'city' => $city,
                'from_date' => $from,
                'to_date' => $to,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (int) $request->get('per_page', 10),
            ],
            'totalDueToday' => $totalDueToday,
        ]);
    }

    public function updateFollowUp(Request $request, int $referredUserId): RedirectResponse
    {
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
        $user = Auth::user();

        if (!$user || !$user->hasRole('SILVERCHANNEL')) {
            abort(403);
        }

        $request->merge(['per_page' => 1000000]);

        $response = $this->index($request);

        /** @var \Illuminate\View\View $response */
        $prospects = $response->getData()['prospects'];

        $filename = 'my-referrals-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = static function () use ($prospects): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Lengkap', 'Email', 'Whatsapp', 'Asal Kota', 'Status', 'Last Follow Up', 'Next Follow Up']);

            foreach ($prospects as $prospect) {
                /** @var User $prospect */
                $followUp = $prospect->referralFollowUps->first();

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

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

