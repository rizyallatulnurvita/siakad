<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-megaphone me-2"></i>Pengumuman</span>
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.pengumuman.index') : route('pengumuman.index') }}" class="small">Lihat semua</a>
    </div>
    <div class="list-group list-group-flush">
        @forelse ($pengumuman as $p)
            <div class="list-group-item">
                <div class="fw-semibold">{{ $p->judul }}</div>
                <div class="small text-muted mb-1">{{ $p->tanggal_mulai->translatedFormat('d F Y') }}</div>
                <div class="small">{{ \Illuminate\Support\Str::limit($p->isi, 160) }}</div>
            </div>
        @empty
            <div class="list-group-item text-muted small py-4 text-center">Belum ada pengumuman aktif.</div>
        @endforelse
    </div>
</div>
