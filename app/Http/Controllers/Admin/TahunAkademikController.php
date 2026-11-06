<?php

namespace App\Http\Controllers\Admin;

use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TahunAkademikController extends CrudController
{
    protected function config(): array
    {
        $fmt = fn ($d) => $d?->format('d/m/Y') ?? '-';

        return [
            'model' => TahunAkademik::class, 'title' => 'Tahun Akademik', 'route' => 'admin.tahun-akademik',
            'icon' => 'bi-calendar3', 'search' => ['tahun'], 'order' => ['tanggal_mulai', 'desc'],
            'filters' => [['name' => 'semester', 'label' => 'Semester', 'options' => ['ganjil' => 'Ganjil', 'genap' => 'Genap']]],
            'columns' => [
                ['label' => 'Tahun', 'key' => 'tahun'],
                ['label' => 'Semester', 'key' => 'semester', 'badge' => true],
                ['label' => 'Mulai', 'value' => fn ($r) => $fmt($r->tanggal_mulai)],
                ['label' => 'Selesai', 'value' => fn ($r) => $fmt($r->tanggal_selesai)],
                ['label' => 'Periode KRS', 'value' => fn ($r) => $r->mulai_krs ? $fmt($r->mulai_krs) . ' – ' . $fmt($r->selesai_krs) : 'Selalu terbuka'],
                ['label' => 'Periode Nilai', 'value' => fn ($r) => $r->mulai_nilai ? $fmt($r->mulai_nilai) . ' – ' . $fmt($r->selesai_nilai) : 'Selalu terbuka'],
                ['label' => 'Status', 'value' => fn ($r) => $r->is_active ? 'aktif' : 'nonaktif', 'badge' => true],
            ],
            'fields' => [
                ['name' => 'tahun', 'label' => 'Tahun Akademik (mis. 2026/2027)', 'type' => 'text', 'required' => true],
                ['name' => 'semester', 'label' => 'Semester', 'type' => 'select', 'options' => ['ganjil' => 'Ganjil', 'genap' => 'Genap'], 'required' => true],
                ['name' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'type' => 'date', 'required' => true],
                ['name' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'type' => 'date', 'required' => true],
                ['name' => 'mulai_krs', 'label' => 'Mulai Pengisian KRS', 'type' => 'date', 'help' => 'Kosongkan bila KRS selalu terbuka.'],
                ['name' => 'selesai_krs', 'label' => 'Akhir Pengisian KRS', 'type' => 'date'],
                ['name' => 'mulai_nilai', 'label' => 'Mulai Input Nilai', 'type' => 'date', 'help' => 'Kosongkan bila input nilai selalu terbuka.'],
                ['name' => 'selesai_nilai', 'label' => 'Akhir Input Nilai', 'type' => 'date'],
                ['name' => 'is_active', 'label' => 'Jadikan semester aktif (semester lain otomatis dinonaktifkan)', 'type' => 'checkbox', 'col' => 12],
            ],
        ];
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'tahun' => ['required', 'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('tahun_akademik', 'tahun')->where('semester', request('semester'))->ignore($model?->id)],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'mulai_krs' => ['nullable', 'date', 'required_with:selesai_krs'],
            'selesai_krs' => ['nullable', 'date', 'after_or_equal:mulai_krs', 'required_with:mulai_krs'],
            'mulai_nilai' => ['nullable', 'date', 'required_with:selesai_nilai'],
            'selesai_nilai' => ['nullable', 'date', 'after_or_equal:mulai_nilai', 'required_with:mulai_nilai'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function extraValidate(array $data, ?Model $model): void
    {
        [$a, $b] = array_map('intval', explode('/', $data['tahun']));
        if ($b !== $a + 1) {
            throw ValidationException::withMessages(['tahun' => 'Tahun kedua harus tepat satu tahun setelah tahun pertama.']);
        }
    }

    protected function beforeSave(array $data, ?Model $model, Request $request): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        if ($data['is_active']) {
            TahunAkademik::query()
                ->when($model, fn ($q) => $q->where('id', '!=', $model->id))
                ->update(['is_active' => false]);
        }

        return $data;
    }
}
