<?php

namespace App\Http\Controllers;

use App\Exceptions\TransferException;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function sent(Request $request, TransferService $service): View
    {
        $service->expireDue();

        return view('student.transfers-sent', [
            'transfers' => $request->user()->sentTransfers()
                ->with(['receiver', 'foodToken.order.items', 'foodToken.order.outlet'])
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function received(Request $request, TransferService $service): View
    {
        $service->expireDue();

        return view('student.transfers-received', [
            'transfers' => $request->user()->receivedTransfers()
                ->with(['sender', 'foodToken.order.items', 'foodToken.order.outlet'])
                ->orderByRaw("case when status = 'pending' then 0 else 1 end")
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function accept(Request $request, TransferService $service, int $id): RedirectResponse
    {
        $request->user()->receivedTransfers()->findOrFail($id);

        try {
            $service->accept($request->user(), $id);
        } catch (TransferException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('student.tokens')
            ->with('status', 'Transfer accepted. The token is now yours and appears below.');
    }

    public function reject(Request $request, TransferService $service, int $id): RedirectResponse
    {
        $request->user()->receivedTransfers()->findOrFail($id);

        try {
            $service->reject($request->user(), $id);
        } catch (TransferException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Transfer request rejected.');
    }

    public function cancel(Request $request, TransferService $service, int $id): RedirectResponse
    {
        $request->user()->sentTransfers()->findOrFail($id);

        try {
            $service->cancel($request->user(), $id);
        } catch (TransferException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Transfer request cancelled.');
    }
}