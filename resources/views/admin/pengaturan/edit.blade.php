@extends('layouts.app')
@section('title', 'Pengaturan Akademik')
@section('content')
<form method="POST" action="{{ route('admin.pengaturan.update') }}">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">Bobot Penilaian (total harus 100)</div>
                <div class="card-body row g-3">
                    @foreach (['tugas' => 'Tugas', 'uts' => 'UTS', 'uas' => 'UAS'] as $k => $l)
                        <div class="col-4"><label class="form-label">{{ $l }} (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="bobot_{{ $k }}" value="{{ old('bobot_' . $k, $weights[$k]) }}" class="form-control"></div>
                    @endforeach
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Batas SKS KRS & IPK</div>
                <div class="card-body row g-3">
                    <div class="col-6"><label class="form-label">Maks. SKS standar</label>
                        <input type="number" min="1" max="40" name="maks_sks_default" value="{{ old('maks_sks_default', $maksSksDefault) }}" class="form-control"></div>
                    <div class="col-6"><label class="form-label">Maks. SKS bila IP lebih tinggi</label>
                        <input type="number" min="1" max="40" name="maks_sks_tinggi" value="{{ old('maks_sks_tinggi', $maksSksTinggi) }}" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Batas IP semester sebelumnya untuk SKS lebih tinggi</label>
                        <input type="number" step="0.01" min="0" max="4" name="ips_batas_tinggi" value="{{ old('ips_batas_tinggi', $ipsBatas) }}" class="form-control"></div>
                    <div class="col-12">
                        <input type="hidden" name="ipk_nilai_terbaik" value="0">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="nt" name="ipk_nilai_terbaik" value="1" @checked(old('ipk_nilai_terbaik', $nilaiTerbaik))>
                            <label class="form-check-label" for="nt">IPK memakai nilai terbaik bila mata kuliah diulang</label></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Konversi Nilai Huruf</div>
                <div class="card-body">
                    <p class="small text-muted">Nilai akhir ≥ batas minimum akan mendapat huruf dan bobot pada baris tersebut. Harus ada satu baris dengan minimum 0. Kosongkan baris untuk menghapusnya.</p>
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Huruf</th><th>Nilai minimum</th><th>Bobot (0–4)</th></tr></thead>
                        <tbody>
                        @foreach ($scale as $i => $row)
                            <tr>
                                <td><input name="skala[{{ $i }}][huruf]" value="{{ old("skala.$i.huruf", $row['huruf']) }}" maxlength="3" class="form-control form-control-sm"></td>
                                <td><input type="number" step="0.01" name="skala[{{ $i }}][min]" value="{{ old("skala.$i.min", $row['min']) }}" class="form-control form-control-sm"></td>
                                <td><input type="number" step="0.01" name="skala[{{ $i }}][bobot]" value="{{ old("skala.$i.bobot", $row['bobot']) }}" class="form-control form-control-sm"></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Pengaturan</button></div>
</form>
@endsection
