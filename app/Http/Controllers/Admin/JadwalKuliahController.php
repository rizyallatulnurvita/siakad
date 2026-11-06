<?php

namespace App\Http\Controllers\Admin;

use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JadwalKuliahController extends CrudController
{
    protected function config(): array
    {
        $kelas = KelasKuliah::with(['mataKuliah', 'tahunAkademik'])->orderByDesc('tahun_akademik_id')->get()
            ->mapWithKeys(fn ($k) => [$k->id => $k->label])->all();

        return [
            'model' => JadwalKuliah::class, 'title' => 'Jadwal Kuliah', 'route' => 'admin.jadwal-kuliah', 'icon' => 'bi-calendar-week',
            'with' => ['kelas.mataKuliah', 'kelas.dosen', 'kelas.tahunAkademik'],
            'search' => ['ruang', 'kelas.mataKuliah.nama', 'kelas.mataKuliah.kode', 'kelas.dosen.nama'],
            'filters' => [['name' => 'hari', 'label' => 'Hari', 'options' => JadwalKuliah::HARI]],
            'order' => function ($q) {
                $q->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")->orderBy('jam_mulai');
            },
            'columns' => [
                ['label' => 'Hari', 'key' => 'hari'],
                ['label' => 'Jam', 'key' => 'jam'],
                ['label' => 'Ruang', 'key' => 'ruang'],
                ['label' => 'Kelas', 'value' => fn ($r) => $r->kelas->label],
                ['label' => 'Dosen', 'key' => 'kelas.dosen.nama'],
            ],
            'fields' => [
                ['name' => 'kelas_kuliah_id', 'label' => 'Kelas Kuliah', 'type' => 'select', 'options' => $kelas, 'required' => true, 'col' => 12],
                ['name' => 'hari', 'label' => 'Hari', 'type' => 'select', 'options' => JadwalKuliah::HARI, 'required' => true],
                ['name' => 'ruang', 'label' => 'Ruang', 'type' => 'text', 'required' => true],
                ['name' => 'jam_mulai', 'label' => 'Jam Mulai', 'type' => 'time', 'required' => true],
                ['name' => 'jam_selesai', 'label' => 'Jam Selesai', 'type' => 'time', 'required' => true],
            ],
        ];
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'kelas_kuliah_id' => ['required', 'exists:kelas_kuliah,id'],
            'hari' => ['required', Rule::in(array_keys(JadwalKuliah::HARI))],
            'ruang' => ['required', 'string', 'max:50'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ];
    }

    /** Deteksi bentrok: kelas yang sama, ruang yang sama, dan dosen yang sama pada semester yang sama. */
    protected function extraValidate(array $data, ?Model $model): void
    {
        $kelas = KelasKuliah::findOrFail($data['kelas_kuliah_id']);

        $base = JadwalKuliah::query()
            ->whereHas('kelas', fn ($k) => $k->where('tahun_akademik_id', $kelas->tahun_akademik_id))
            ->where('hari', $data['hari'])
            ->where('jam_mulai', '<', $data['jam_selesai'])
            ->where('jam_selesai', '>', $data['jam_mulai'])
            ->when($model, fn ($q) => $q->where('id', '!=', $model->id));

        $errors = [];
        if ((clone $base)->where('kelas_kuliah_id', $kelas->id)->exists()) {
            $errors['jam_mulai'] = 'Kelas ini sudah memiliki jadwal lain pada waktu yang beririsan.';
        }
        if ((clone $base)->where('ruang', $data['ruang'])->exists()) {
            $errors['ruang'] = 'Ruang sudah dipakai kelas lain pada waktu tersebut.';
        }
        if ((clone $base)->whereHas('kelas', fn ($k) => $k->where('dosen_id', $kelas->dosen_id))->exists()) {
            $errors['kelas_kuliah_id'] = 'Dosen pengampu sudah memiliki jadwal mengajar pada waktu tersebut.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
