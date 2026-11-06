<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pengumuman;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengumumanController extends CrudController
{
    protected function config(): array
    {
        return [
            'model' => Pengumuman::class, 'title' => 'Pengumuman', 'route' => 'admin.pengumuman', 'icon' => 'bi-megaphone',
            'with' => ['penulis'], 'search' => ['judul', 'isi'], 'order' => ['tanggal_mulai', 'desc'],
            'filters' => [['name' => 'target', 'label' => 'Target', 'options' => Pengumuman::TARGET]],
            'columns' => [
                ['label' => 'Judul', 'key' => 'judul'],
                ['label' => 'Target', 'key' => 'target', 'badge' => true],
                ['label' => 'Periode', 'value' => fn ($r) => $r->tanggal_mulai->format('d/m/Y') . ' – ' . ($r->tanggal_selesai?->format('d/m/Y') ?? 'tanpa batas')],
                ['label' => 'Status', 'value' => function ($r) {
                    $t = now()->startOfDay();

                    return $r->tanggal_mulai->gt($t) ? 'terjadwal' : (($r->tanggal_selesai && $r->tanggal_selesai->lt($t)) ? 'berakhir' : 'aktif');
                }, 'badge' => true],
            ],
            'fields' => [
                ['name' => 'judul', 'label' => 'Judul', 'type' => 'text', 'required' => true, 'col' => 12],
                ['name' => 'isi', 'label' => 'Isi Pengumuman', 'type' => 'textarea', 'required' => true, 'col' => 12, 'rows' => 6],
                ['name' => 'target', 'label' => 'Ditujukan kepada', 'type' => 'select', 'options' => Pengumuman::TARGET, 'required' => true],
                ['name' => 'tanggal_mulai', 'label' => 'Tanggal Mulai Tayang', 'type' => 'date', 'required' => true, 'default' => now()->toDateString()],
                ['name' => 'tanggal_selesai', 'label' => 'Tanggal Selesai (opsional)', 'type' => 'date'],
            ],
        ];
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:10000'],
            'target' => ['required', Rule::in(array_keys(Pengumuman::TARGET))],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ];
    }

    protected function beforeSave(array $data, ?Model $model, Request $request): array
    {
        if (!$model) {
            $data['user_id'] = $request->user()->id;
        }

        return $data;
    }
}
