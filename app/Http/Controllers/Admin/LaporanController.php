<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\http\Services\GradeService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public const REPORTS = [
        'mahasiswa' => ['title' => 'Laporan Mahasiswa', 'icon' => 'bi-mortarboard', 'desc' => 'Daftar mahasiswa per program studi, angkatan, dan status.'],
        'dosen' => ['title' => 'Laporan Dosen', 'icon' => 'bi-person-workspace', 'desc' => 'Daftar dosen per program studi dan status.'],
        'program-studi' => ['title' => 'Laporan Program Studi', 'icon' => 'bi-building', 'desc' => 'Rekap jumlah mahasiswa, dosen, dan mata kuliah.'],
        'mata-kuliah' => ['title' => 'Laporan Mata Kuliah', 'icon' => 'bi-journal-bookmark', 'desc' => 'Daftar mata kuliah dan SKS.'],
        'jadwal' => ['title' => 'Laporan Jadwal Kuliah', 'icon' => 'bi-calendar-week', 'desc' => 'Jadwal perkuliahan per semester.'],
        'krs' => ['title' => 'Rekap KRS', 'icon' => 'bi-card-checklist', 'desc' => 'Rekap pengajuan dan persetujuan KRS.'],
        'nilai' => ['title' => 'Rekap Nilai', 'icon' => 'bi-clipboard-data', 'desc' => 'Rekap nilai per semester atau kelas.'],
        'khs' => ['title' => 'KHS Mahasiswa', 'icon' => 'bi-file-earmark-text', 'desc' => 'Kartu Hasil Studi per mahasiswa dan semester.'],
        'ip-ipk' => ['title' => 'Rekap IP dan IPK', 'icon' => 'bi-graph-up', 'desc' => 'IP semester dan IPK seluruh mahasiswa.'],
    ];

    public function index()
    {
        return view('admin.laporan.index', ['reports' => self::REPORTS]);
    }

    public function show(Request $request, string $jenis)
    {
        abort_unless(isset(self::REPORTS[$jenis]), 404);

        $data = match ($jenis) {
            'mahasiswa' => $this->mahasiswa($request),
            'dosen' => $this->dosen($request),
            'program-studi' => $this->prodi($request),
            'mata-kuliah' => $this->mataKuliah($request),
            'jadwal' => $this->jadwal($request),
            'krs' => $this->krs($request),
            'nilai' => $this->nilai($request),
            'khs' => $this->khs($request),
            'ip-ipk' => $this->ipIpk($request),
        };

        if ($request->input('export') === 'csv') {
            return $this->csv($data, $jenis);
        }

        return view('admin.laporan.show', $data + ['jenis' => $jenis, 'title' => self::REPORTS[$jenis]['title']]);
    }

    // ---------------------------------------------------------------- helpers

    private function prodiOptions(): array
    {
        return ProgramStudi::orderBy('nama')->pluck('nama', 'id')->all();
    }

    private function taOptions(): array
    {
        return TahunAkademik::orderByDesc('tanggal_mulai')->get()->pluck('label', 'id')->all();
    }

    private function taId(Request $r): ?string
    {
        return $r->has('tahun_akademik_id') ? $r->input('tahun_akademik_id') : (string) TahunAkademik::aktif()?->id;
    }

    private function num($v): string
    {
        return $v === null ? '-' : rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    }

    private function csv(array $d, string $jenis)
    {
        $safe = fn ($v) => is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@'], true) ? "'" . $v : $v;

        return response()->streamDownload(function () use ($d, $safe) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $d['headings']);
            foreach ($d['rows'] as $row) {
                fputcsv($out, array_map($safe, $row));
            }
            foreach ($d['summary'] ?? [] as $label => $value) {
                fputcsv($out, [$label, $value]);
            }
            fclose($out);
        }, "laporan-{$jenis}-" . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---------------------------------------------------------------- laporan

    private function mahasiswa(Request $r): array
    {
        $q = Mahasiswa::with('programStudi')->orderBy('nim')
            ->when($r->filled('program_studi_id'), fn ($x) => $x->where('program_studi_id', $r->program_studi_id))
            ->when($r->filled('angkatan'), fn ($x) => $x->where('angkatan', $r->angkatan))
            ->when($r->filled('status'), fn ($x) => $x->where('status', $r->status));

        return [
            'filters' => [
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $this->prodiOptions()],
                ['name' => 'angkatan', 'label' => 'Angkatan', 'type' => 'text'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => Mahasiswa::STATUS],
            ],
            'headings' => ['NIM', 'Nama', 'Program Studi', 'Angkatan', 'L/P', 'Telepon', 'Status'],
            'rows' => $q->get()->map(fn ($m) => [$m->nim, $m->nama, $m->programStudi->nama, $m->angkatan, $m->jenis_kelamin, $m->telepon ?? '-', ucfirst($m->status)])->all(),
        ];
    }

    private function dosen(Request $r): array
    {
        $q = Dosen::with('programStudi')->orderBy('nama')
            ->when($r->filled('program_studi_id'), fn ($x) => $x->where('program_studi_id', $r->program_studi_id))
            ->when($r->filled('status'), fn ($x) => $x->where('status', $r->status));

        return [
            'filters' => [
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $this->prodiOptions()],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => Dosen::STATUS],
            ],
            'headings' => ['NIDN', 'Nama', 'Email', 'Program Studi', 'Telepon', 'Status'],
            'rows' => $q->get()->map(fn ($d) => [$d->nidn, $d->nama, $d->email, $d->programStudi->nama, $d->telepon ?? '-', ucfirst($d->status)])->all(),
        ];
    }

    private function prodi(Request $r): array
    {
        $rows = ProgramStudi::withCount([
            'mahasiswa as mhs_aktif' => fn ($q) => $q->where('status', 'aktif'),
            'dosen as dosen_aktif' => fn ($q) => $q->where('status', 'aktif'),
            'mataKuliah',
        ])->orderBy('kode')->get();

        return [
            'filters' => [],
            'headings' => ['Kode', 'Program Studi', 'Jenjang', 'Mahasiswa Aktif', 'Dosen Aktif', 'Mata Kuliah'],
            'rows' => $rows->map(fn ($p) => [$p->kode, $p->nama, $p->jenjang, $p->mhs_aktif, $p->dosen_aktif, $p->mata_kuliah_count])->all(),
        ];
    }

    private function mataKuliah(Request $r): array
    {
        $q = MataKuliah::with('programStudi')->orderBy('kode')
            ->when($r->filled('program_studi_id'), fn ($x) => $x->where('program_studi_id', $r->program_studi_id));

        return [
            'filters' => [['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $this->prodiOptions()]],
            'headings' => ['Kode', 'Mata Kuliah', 'SKS', 'Program Studi', 'Semester'],
            'rows' => $q->get()->map(fn ($m) => [$m->kode, $m->nama, $m->sks, $m->programStudi->nama, $m->semester])->all(),
        ];
    }

    private function jadwal(Request $r): array
    {
        $ta = $this->taId($r);
        $q = JadwalKuliah::with(['kelas.mataKuliah', 'kelas.dosen'])
            ->when($ta, fn ($x) => $x->whereHas('kelas', fn ($k) => $k->where('tahun_akademik_id', $ta)))
            ->when($r->filled('program_studi_id'), fn ($x) => $x->whereHas('kelas.mataKuliah', fn ($m) => $m->where('program_studi_id', $r->program_studi_id)))
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")->orderBy('jam_mulai');

        return [
            'filters' => [
                ['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik', 'type' => 'select', 'options' => $this->taOptions(), 'value' => $ta],
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $this->prodiOptions()],
            ],
            'headings' => ['Hari', 'Jam', 'Ruang', 'Kode', 'Mata Kuliah', 'Kelas', 'Dosen'],
            'rows' => $q->get()->map(fn ($j) => [$j->hari, $j->jam, $j->ruang, $j->kelas->mataKuliah->kode, $j->kelas->mataKuliah->nama, $j->kelas->nama, $j->kelas->dosen->nama])->all(),
        ];
    }

    private function krs(Request $r): array
    {
        $ta = $this->taId($r);
        $q = Krs::with(['mahasiswa.programStudi', 'tahunAkademik'])
            ->when($ta, fn ($x) => $x->where('tahun_akademik_id', $ta))
            ->when($r->filled('status'), fn ($x) => $x->where('status', $r->status))
            ->when($r->filled('program_studi_id'), fn ($x) => $x->whereHas('mahasiswa', fn ($m) => $m->where('program_studi_id', $r->program_studi_id)))
            ->orderBy('id');

        $rows = $q->get();

        return [
            'filters' => [
                ['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik', 'type' => 'select', 'options' => $this->taOptions(), 'value' => $ta],
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $this->prodiOptions()],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft', 'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak']],
            ],
            'headings' => ['NIM', 'Nama', 'Program Studi', 'Semester', 'SKS', 'Status', 'Diajukan'],
            'rows' => $rows->map(fn ($k) => [$k->mahasiswa->nim, $k->mahasiswa->nama, $k->mahasiswa->programStudi->nama, $k->tahunAkademik->label, $k->total_sks, ucfirst($k->status), $k->diajukan_at?->format('d/m/Y H:i') ?? '-'])->all(),
            'summary' => $rows->groupBy('status')->map->count()->mapWithKeys(fn ($n, $s) => ['Jumlah KRS ' . $s => $n])->all(),
        ];
    }

    private function nilai(Request $r): array
    {
        $ta = $this->taId($r);
        $kelas = KelasKuliah::with(['mataKuliah', 'tahunAkademik'])
            ->when($ta, fn ($x) => $x->where('tahun_akademik_id', $ta))->get()
            ->mapWithKeys(fn ($k) => [$k->id => $k->label])->all();

        $q = Nilai::with(['mahasiswa', 'kelas.mataKuliah'])
            ->when($r->filled('kelas_kuliah_id'), fn ($x) => $x->where('kelas_kuliah_id', $r->kelas_kuliah_id))
            ->when($ta, fn ($x) => $x->whereHas('kelas', fn ($k) => $k->where('tahun_akademik_id', $ta)))
            ->orderBy('kelas_kuliah_id')->orderBy('id');

        return [
            'filters' => [
                ['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik', 'type' => 'select', 'options' => $this->taOptions(), 'value' => $ta],
                ['name' => 'kelas_kuliah_id', 'label' => 'Kelas', 'type' => 'select', 'options' => $kelas],
            ],
            'headings' => ['NIM', 'Nama', 'Mata Kuliah', 'Kelas', 'Tugas', 'UTS', 'UAS', 'Nilai Akhir', 'Huruf'],
            'rows' => $q->get()->map(fn ($n) => [$n->mahasiswa->nim, $n->mahasiswa->nama, $n->kelas->mataKuliah->nama, $n->kelas->nama, $this->num($n->tugas), $this->num($n->uts), $this->num($n->uas), $this->num($n->nilai_akhir), $n->huruf ?? '-'])->all(),
        ];
    }

    private function khs(Request $r): array
    {
        $ta = $this->taId($r);
        $data = [
            'filters' => [
                ['name' => 'nim', 'label' => 'NIM Mahasiswa', 'type' => 'text'],
                ['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik', 'type' => 'select', 'options' => $this->taOptions(), 'value' => $ta, 'required' => true],
            ],
            'headings' => ['Kode', 'Mata Kuliah', 'SKS', 'Nilai Akhir', 'Huruf', 'Bobot', 'Mutu (SKS x Bobot)'],
            'rows' => [],
            'note' => 'Masukkan NIM dan pilih tahun akademik untuk menampilkan KHS.',
        ];
        if (!$r->filled('nim') || !$ta) {
            return $data;
        }

        $m = Mahasiswa::with('programStudi')->where('nim', $r->nim)->first();
        if (!$m) {
            $data['note'] = 'Mahasiswa dengan NIM tersebut tidak ditemukan.';

            return $data;
        }

        $g = new GradeService();
        $all = $g->rows($m->id);
        $sem = $all->where('ta_id', (int) $ta);
        $ips = $g->ringkas($sem);
        $ipk = $g->ringkasKumulatif($all);

        $data['note'] = null;
        $data['judul_tambahan'] = "{$m->nim} — {$m->nama} ({$m->programStudi->nama})";
        $data['rows'] = $sem->map(fn ($x) => [$x->kode, $x->mk_nama, $x->sks, $this->num($x->nilai_akhir), $x->huruf, $this->num($x->bobot), $this->num($x->sks * $x->bobot)])->values()->all();
        $data['summary'] = [
            'SKS semester' => $ips['sks'], 'IP semester' => $this->num($ips['ip']),
            'Total SKS kumulatif' => $ipk['sks'], 'SKS lulus' => $ipk['sks_lulus'], 'IPK' => $this->num($ipk['ip']),
        ];

        return $data;
    }

    private function ipIpk(Request $r): array
    {
        $ta = $this->taId($r);
        $mhs = Mahasiswa::with('programStudi')->orderBy('nim')
            ->when($r->filled('program_studi_id'), fn ($x) => $x->where('program_studi_id', $r->program_studi_id))
            ->get();

        $g = new GradeService();
        $byMhs = $g->rows($mhs->pluck('id')->all())->groupBy('mahasiswa_id');

        $rows = [];
        foreach ($mhs as $m) {
            $nilai = $byMhs->get($m->id, collect());
            if ($nilai->isEmpty()) {
                continue;
            }
            $ips = $g->ringkas($ta ? $nilai->where('ta_id', (int) $ta) : []);
            $ipk = $g->ringkasKumulatif($nilai);
            $rows[] = [$m->nim, $m->nama, $m->programStudi->nama, $ips['sks'], $ta ? $this->num($ips['ip']) : '-', $ipk['sks'], $ipk['sks_lulus'], $this->num($ipk['ip'])];
        }

        return [
            'filters' => [
                ['name' => 'tahun_akademik_id', 'label' => 'Tahun Akademik (untuk IP)', 'type' => 'select', 'options' => $this->taOptions(), 'value' => $ta],
                ['name' => 'program_studi_id', 'label' => 'Program Studi', 'type' => 'select', 'options' => $this->prodiOptions()],
            ],
            'headings' => ['NIM', 'Nama', 'Program Studi', 'SKS Semester', 'IP Semester', 'Total SKS', 'SKS Lulus', 'IPK'],
            'rows' => $rows,
        ];
    }
}
