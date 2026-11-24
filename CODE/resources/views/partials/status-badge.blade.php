@php
    $colors = ['Pending' => 'secondary', 'Confirmed' => 'primary', 'Shipping' => 'info', 'Completed' => 'success', 'Cancelled' => 'danger'];
@endphp
<span class="badge rounded-pill status-badge text-bg-{{ $colors[$status] ?? 'secondary' }}">{{ $status }}</span>
