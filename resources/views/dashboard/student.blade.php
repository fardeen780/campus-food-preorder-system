@extends('layouts.app')
@section('title', 'Home')
@section('content')
    @php
        $user = auth()->user();
        $firstName = \Illuminate\Support\Str::before($user->name, ' ');
        $heroImage = is_file(public_path('images/hero.webp')) ? asset('images/hero.webp') : null;
    @endphp

    <section class="hero-banner mb-4" aria-labelledby="welcomeHeading">
        <div class="row g-0">
            <div class="{{ $heroImage ? 'col-lg-7' : 'col-12' }} hero-copy">
                <p class="hero-id">Student ID {{ $user->student_id }}</p>
                <h1 id="welcomeHeading" class="hero-title">Hello, {{ $firstName }}</h1>
                <p class="hero-text">Order your meal ahead, skip the queue and collect it at the time you choose.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('student.menu') }}" class="btn btn-hero btn-lg">Browse the menu</a>
                    <a href="{{ route('student.orders') }}" class="btn btn-outline-light btn-lg">My orders</a>
                </div>
            </div>
            @if ($heroImage)
                <div class="col-lg-5 d-none d-lg-block hero-photo">
                    <img src="{{ $heroImage }}" alt="Freshly prepared meals from the campus cafeteria">
                </div>
            @endif
        </div>
    </section>

    <section class="row g-3 mb-4" aria-label="Your summary">
        <x-stat-card cols="col-6 col-lg-3" label="Total orders" :value="$totalOrders" icon="bi-receipt" />
        <x-stat-card cols="col-6 col-lg-3" label="Active orders" :value="$activeOrders" icon="bi-hourglass-split" />
        <x-stat-card cols="col-6 col-lg-3" label="Available food tokens" :value="$tokensHeld" icon="bi-ticket-perforated" />
        <x-stat-card cols="col-6 col-lg-3" label="Outstanding dues" :value="'৳'.number_format((float) $outstandingDues, 2)" icon="bi-wallet2" />
    </section>

    @if ($pendingRequests->isNotEmpty())
        <section class="mb-4" aria-labelledby="pendingHeading">
            <div class="card border-0 shadow-sm alert-panel">
                <div class="card-body">
                    <h2 id="pendingHeading" class="h5">Transfer requests waiting for you</h2>
                    <p class="text-muted small mb-2">If you accept, the food token becomes yours. The sender stays responsible for paying for the order.</p>
                    @foreach ($pendingRequests as $pending)
                        @php $pendingOrder = $pending->foodToken->order; @endphp
                        <div class="request-row">
                            <div>
                                <div class="fw-bold">{{ $pending->sender->name }} <span class="text-muted fw-normal">(ID {{ $pending->sender->student_id }})</span></div>
                                <div class="small">{{ $pendingOrder->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', ') }} from {{ $pendingOrder->outlet->name }}</div>
                                <div class="small text-muted">Pickup {{ $pendingOrder->pickup_datetime->format('D j M, g:i A') }}. Expires {{ $pending->expires_at->format('j M, g:i A') }}.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('student.transfers.accept', $pending->id) }}"
                                      onsubmit="return confirm('Accept this token? You will become its holder and can collect the food. The sender stays responsible for the payment.')">
                                    @csrf
                                    <button class="btn btn-brand btn-sm" type="submit">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('student.transfers.reject', $pending->id) }}"
                                      onsubmit="return confirm('Reject this transfer request?')">
                                    @csrf
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Reject</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="mb-5" aria-labelledby="menuHeading">
        <div class="section-head">
            <h2 id="menuHeading">Order food</h2>
            <a href="{{ route('student.menu') }}" class="fw-semibold">See the full menu</a>
        </div>
        <form method="GET" action="{{ route('student.menu') }}" class="search-bar mb-3" role="search">
            <i class="bi bi-search"></i>
            <input type="search" name="q" maxlength="50" placeholder="Search for a dish" aria-label="Search for a dish">
            <button class="btn btn-brand" type="submit">Search</button>
        </form>
        <div class="chips">
            <a class="chip" href="{{ route('student.menu') }}">All food</a>
            @foreach ($categories as $category)
                <a class="chip" href="{{ route('student.menu', ['category' => $category->id]) }}">{{ $category->name }}</a>
            @endforeach
        </div>

        @if ($featured->isEmpty())
            <div class="empty-state">No food is on the menu yet. Please check back soon.</div>
        @else
            <div class="row g-3">
                @foreach ($featured as $item)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <x-food-card :item="$item" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="row g-4 mb-5">
        <section class="col-lg-8" aria-labelledby="ordersHeading">
            <div class="section-head">
                <h2 id="ordersHeading">Recent orders</h2>
                <a href="{{ route('student.orders') }}" class="fw-semibold">All orders</a>
            </div>
            @if ($recentOrders->isEmpty())
                <div class="empty-state">You have not placed an order yet. Pick something from the menu to start.</div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr><th>Order</th><th>Outlet</th><th>Pickup</th><th>Total</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td class="fw-bold">{{ $order->order_number }}</td>
                                        <td>{{ $order->outlet->name }}</td>
                                        <td>{{ $order->pickup_datetime->format('D j M, g:i A') }}</td>
                                        <td>৳{{ number_format((float) $order->total_amount, 2) }}</td>
                                        <td><x-order-badges :order="$order" /></td>
                                        <td class="text-end"><a href="{{ route('student.orders.show', $order->id) }}" class="btn btn-light btn-sm">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        <section class="col-lg-4" aria-labelledby="tokensHeading">
            <div class="section-head">
                <h2 id="tokensHeading">My food tokens</h2>
                <a href="{{ route('student.tokens') }}" class="fw-semibold">All tokens</a>
            </div>
            @forelse ($tokens as $token)
                <div class="token-mini mb-2">
                    <div class="d-flex justify-content-between small">
                        <span class="fw-semibold">{{ $token->order->outlet->name }}</span>
                        <span class="text-muted">{{ $token->order->order_number }}</span>
                    </div>
                    <div class="token-code">{{ $token->token_code }}</div>
                    <div class="small text-muted">Pickup {{ $token->order->pickup_datetime->format('D j M, g:i A') }}</div>
                    @if ((int) $token->order->original_student_id !== (int) $user->id)
                        <div class="small">Transferred to you</div>
                    @endif
                </div>
            @empty
                <div class="empty-state small">You do not hold an active food token right now.</div>
            @endforelse
            <a href="{{ route('student.tokens') }}" class="btn btn-outline-success w-100 mt-2">Manage and transfer tokens</a>
        </section>
    </div>

    <div class="row g-4">
        <section class="col-lg-6" aria-labelledby="transfersHeading">
            <div class="section-head"><h2 id="transfersHeading">Token transfers</h2></div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('student.transfers.received') }}" class="btn btn-outline-success btn-sm">Received requests</a>
                        <a href="{{ route('student.transfers.sent') }}" class="btn btn-outline-success btn-sm">Sent requests</a>
                    </div>
                    @if ($sentTransfers->isEmpty() && $receivedTransfers->isEmpty())
                        <p class="text-muted mb-0">No transfers yet. To pass a token to another student, open My food tokens and choose Transfer token.</p>
                    @else
                        @if ($sentTransfers->isNotEmpty())
                            <div class="fw-bold small mb-1">You sent</div>
                            @foreach ($sentTransfers as $sent)
                                <div class="d-flex justify-content-between align-items-center small py-1">
                                    <span>To {{ $sent->receiver->name }} for {{ $sent->foodToken->order->order_number }}</span>
                                    <x-transfer-badge :status="$sent->status" />
                                </div>
                            @endforeach
                        @endif
                        @if ($receivedTransfers->isNotEmpty())
                            <div class="fw-bold small mt-3 mb-1">You received</div>
                            @foreach ($receivedTransfers as $received)
                                <div class="d-flex justify-content-between align-items-center small py-1">
                                    <span>From {{ $received->sender->name }} for {{ $received->foodToken->order->order_number }}</span>
                                    <x-transfer-badge :status="$received->status" />
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>
        </section>

        <section class="col-lg-6" aria-labelledby="notifHeading">
            <div class="section-head">
                <h2 id="notifHeading">Notifications</h2>
                <a href="{{ route('student.notifications') }}" class="fw-semibold">See all</a>
            </div>
            <div class="card border-0 shadow-sm">
                <ul class="list-group list-group-flush">
                    @forelse ($notifications as $n)
                        <li class="list-group-item {{ $n->read_at ? '' : 'notice-unread' }}">
                            <div class="{{ $n->read_at ? '' : 'fw-bold' }}">{{ $n->data['title'] ?? 'Notification' }}</div>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($n->data['message'] ?? '', 110) }}</div>
                            <div class="small text-muted">{{ $n->created_at->diffForHumans() }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">You are all caught up.</li>
                    @endforelse
                </ul>
            </div>
        </section>
    </div>
@endsection