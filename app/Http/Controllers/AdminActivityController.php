<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'action' => ['nullable', 'string', 'max:50'],
        ]);

        $query = ActivityLog::query()->with('user');

        if (! empty($f['action'])) {
            $query->where('action', $f['action']);
        }

        return view('admin.activity', [
            'logs' => $query->orderByDesc('id')->paginate(25)->withQueryString(),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}