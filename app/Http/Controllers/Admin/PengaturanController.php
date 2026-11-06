<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\http\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengaturanController extends Controller
{
    public function edit()
    {
        $g = new GradeService();

        return view('admin.pengaturan.edit', [
            'weights' => $g->weights(),
            'scale' => array_pad($g->scale(), count($g->scale()) + 2, ['huruf' => '', 'min' => '', 'bobot' => '']),
            'maksSksDefault' => Pengaturan::get('maks_sks_default', 20),
            'ipsBatas' => Pengaturan::get('ips_batas_tinggi', 3.0),
            'maksSksTinggi' => Pengaturan::get('maks_sks_tinggi', 24),
            'nilaiTerbaik' => (bool) Pengaturan::get('ipk_nilai_terbaik', true),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'bobot_tugas' => ['required', 'numeric', 'between:0,100'],
            'bobot_uts' => ['required', 'numeric', 'between:0,100'],
            'bobot_uas' => ['required', 'numeric', 'between:0,100'],
            'skala' => ['required', 'array'],
            'skala.*.huruf' => ['nullable', 'string', 'max:3'],
            'skala.*.min' => ['nullable', 'numeric', 'between:0,100', 'required_with:skala.*.huruf'],
            'skala.*.bobot' => ['nullable', 'numeric', 'between:0,4', 'required_with:skala.*.huruf'],
            'maks_sks_default' => ['required', 'integer', 'between:1,40'],
            'ips_batas_tinggi' => ['required', 'numeric', 'between:0,4'],
            'maks_sks_tinggi' => ['required', 'integer', 'between:1,40'],
            'ipk_nilai_terbaik' => ['nullable', 'boolean'],
        ]);

        if (round($data['bobot_tugas'] + $data['bobot_uts'] + $data['bobot_uas'], 2) !== 100.0) {
            throw ValidationException::withMessages(['bobot_tugas' => 'Jumlah bobot Tugas + UTS + UAS harus tepat 100.']);
        }

        $skala = collect($data['skala'])
            ->filter(fn ($r) => !empty($r['huruf']))
            ->map(fn ($r) => ['huruf' => $r['huruf'], 'min' => (float) $r['min'], 'bobot' => (float) $r['bobot']])
            ->sortByDesc('min')->values();

        if ($skala->isEmpty() || $skala->last()['min'] > 0) {
            throw ValidationException::withMessages(['skala' => 'Skala nilai harus memiliki baris dengan batas minimum 0 agar semua nilai dapat dikonversi.']);
        }
        if ($skala->pluck('min')->duplicates()->isNotEmpty() || $skala->pluck('huruf')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['skala' => 'Huruf dan batas minimum pada skala tidak boleh duplikat.']);
        }

        DB::transaction(function () use ($data, $skala) {
            Pengaturan::put('bobot_nilai', [
                'tugas' => (float) $data['bobot_tugas'], 'uts' => (float) $data['bobot_uts'], 'uas' => (float) $data['bobot_uas'],
            ]);
            Pengaturan::put('skala_nilai', $skala->all());
            Pengaturan::put('maks_sks_default', (int) $data['maks_sks_default']);
            Pengaturan::put('ips_batas_tinggi', (float) $data['ips_batas_tinggi']);
            Pengaturan::put('maks_sks_tinggi', (int) $data['maks_sks_tinggi']);
            Pengaturan::put('ipk_nilai_terbaik', (bool) ($data['ipk_nilai_terbaik'] ?? false));
        });

        return back()->with('success', 'Pengaturan akademik disimpan. Perubahan berlaku untuk input nilai berikutnya; nilai lama tidak dihitung ulang otomatis.');
    }
}
