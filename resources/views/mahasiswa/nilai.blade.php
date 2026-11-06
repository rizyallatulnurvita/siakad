@extends('layouts.app')
@section('title', 'Nilai & Transkrip')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4"><x-stat-card title="IPK" :value="$ipk['sks'] > 0 ? number_format($ipk['ip'], 2, ',', '.') : '-'" icon="bi-award" color="warning" /></div>
    <div class="col-md-4"><x-stat-card title="Total SKS Dihitung" :value="$ipk['sks']" icon="bi-journal-text" color="primary" /></div>
    <div class="col-md-4"><x-stat-card title="SKS Lulus" :value="$ipk['sks_lulus']" icon="bi-check2-circle" color="success" /></div>
</div>

@forelse ($perSemester as $rows)
    @php $f = $rows->first(); $s = $g->ringkas($rows); @endphp
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between"><span>{{ $f->tahun }} {{ ucfirst($f->semester) }}</span><span class="text-muted fw-normal">IP {{ number_format($s['ip'], 2, ',', '.') }} · {{ $s['sks'] }} SKS</span></div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Tugas</th><th>UTS</th><th>UAS</th><th>Akhir</th><th>Huruf</th></tr></thead>
            <tbody>
            @foreach ($rows as $r)
                <tr><td>{{ $r->kode }}</td><td>{{ $r->mk_nama }}</td><td>{{ $r->sks }}</td><td>{{ $r->tugas }}</td><td>{{ $r->uts }}</td><td>{{ $r->uas }}</td><td>{{ $r->nilai_akhir }}</td><td>{{ $r->huruf }}</td></tr>
            @endforeach
            </tbody>
        </table></div>
    </div>
@empty
    <div class="card"><div class="card-body text-center text-muted py-5"><i class="bi bi-clipboard-data fs-3 d-block mb-2"></i>Belum ada nilai yang diinput dosen.</div></div>
@endforelse
@endsection
