@props(['id', 'type' => 'bar', 'labels' => [], 'data' => [], 'label' => 'Data', 'colors' => null, 'max' => null])
@php
    $palette = ['#4f46e5', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444', '#a855f7', '#14b8a6', '#f97316'];
    $warna = $colors ?: $palette;
@endphp
<div style="position:relative;height:260px">
    <canvas id="{{ $id }}"></canvas>
</div>
@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endonce
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tipe = @json($type);
    var opsi = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: tipe !== 'bar' } } };
    if (tipe === 'bar' || tipe === 'line') {
        opsi.scales = { y: { beginAtZero: true, @if($max) max: {{ (int) $max }}, @endif ticks: { precision: 0 } } };
    }
    new Chart(document.getElementById(@json($id)), {
        type: tipe,
        data: {
            labels: @json($labels),
            datasets: [{ label: @json($label), data: @json($data), backgroundColor: @json($warna), borderRadius: tipe === 'bar' ? 4 : 0 }]
        },
        options: opsi
    });
});
</script>
