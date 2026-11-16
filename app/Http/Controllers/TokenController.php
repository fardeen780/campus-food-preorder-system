<?php

namespace App\Http\Controllers;

use App\Exceptions\TransferException;
use App\Models\Setting;
use App\Models\TokenTransfer;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function index(Request $request, TransferService $service): View
    {
        $service->expireDue();
        $user = $request->user();

        $tokens = $user->heldTokens()
            ->with(['order.outlet', 'order.items', 'order.originalStudent'])
            ->latest('id')
            ->paginate(10);

        $pending = TokenTransfer::query()
            ->with('receiver')
            ->whereIn('food_token_id', $tokens->pluck('id'))
            ->where('status', 'pending')
            ->get()
            ->keyBy('food_token_id');

        $reasons = $tokens->getCollection()
            ->mapWithKeys(fn ($token) => [$token->id => $service->transferBlockReason($token, $user)]);

        return view('student.tokens', compact('tokens', 'pending', 'reasons'));
    }

    public function create(Request $request, TransferService $service, int $id): View|RedirectResponse
    {
        $service->expireDue();

        $token = $request->user()->heldTokens()
            ->with(['order.outlet', 'order.items'])
            ->findOrFail($id);

        $reason = $service->transferBlockReason($token, $request->user());

        if ($reason !== null) {
            return redirect()->route('student.tokens')->with('error', $reason);
        }

        return view('student.transfer-create', [
            'token' => $token,
            'expiryHours' => max(1, Setting::integer('transfer_expiry_hours')),
        ]);
    }

    public function store(Request $request, TransferService $service, int $id): RedirectResponse
    {
        $data = $request->validate(
            ['student_id' => ['required', 'regex:/^\d{7}$/']],
            ['student_id.regex' => 'Student ID must be exactly 7 digits.']
        );

        $token = $request->user()->heldTokens()->findOrFail($id);

        try {
            $transfer = $service->send($request->user(), $token->id, $data['student_id']);
        } catch (TransferException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $transfer->load('receiver');

        return redirect()->route('student.transfers.sent')
            ->with('status', 'Transfer request sent to '.$transfer->receiver->name.' (ID '.$transfer->receiver->student_id.'). The token stays with you until they accept.');
    }
}