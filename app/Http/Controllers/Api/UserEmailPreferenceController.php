<?php
declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmailPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserEmailPreferenceController extends Controller
{
    public function index()
    {
        $prefs = EmailPreference::where('user_id', Auth::id())->get();
        return response()->json($prefs);
    }

    public function update(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:64',
            'enabled' => 'required|boolean',
        ]);
        $pref = EmailPreference::updateOrCreate([
            'user_id' => Auth::id(),
            'type' => $request->string('type'),
        ], [
            'enabled' => $request->boolean('enabled'),
        ]);
        return response()->json(['success' => true, 'preference' => $pref]);
    }
}

