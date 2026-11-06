<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Basis CRUD yang dapat dipakai ulang. Controller turunan cukup mendefinisikan
 * config() (kolom, field, filter, pencarian) dan rules(). View: resources/views/crud/*.
 */
abstract class CrudController extends Controller
{
    abstract protected function config(): array;

    protected function rules(?Model $model = null): array
    {
        return [];
    }

    protected function cfg(): array
    {
        return array_merge([
            'readonly' => false, 'with' => [], 'search' => [], 'filters' => [],
            'row_actions' => [], 'order' => ['id', 'desc'], 'fields' => [], 'columns' => [],
            'icon' => 'bi-table', 'can_show' => true,
        ], $this->config());
    }

    protected function query(Request $request)
    {
        $c = $this->cfg();

        return $c['model']::query()->with($c['with']);
    }

    /** Dipanggil di dalam transaksi sebelum menyimpan; kembalikan payload untuk model. */
    protected function beforeSave(array $data, ?Model $model, Request $request): array
    {
        return $data;
    }

    protected function afterSave(Model $model, array $data, Request $request): void
    {
    }

    protected function beforeDelete(Model $model): void
    {
    }

    protected function afterDelete(Model $model): void
    {
    }

    protected function extraValidate(array $data, ?Model $model): void
    {
    }

    protected function find($id): Model
    {
        return $this->query(request())->findOrFail($id);
    }

    public function index(Request $request)
    {
        $c = $this->cfg();
        $q = $this->query($request);

        if ($s = trim((string) $request->input('q'))) {
            $like = '%' . addcslashes($s, '%_\\') . '%';
            $q->where(function ($w) use ($c, $like) {
                foreach ($c['search'] as $col) {
                    if (str_contains($col, '.')) {
                        $pos = strrpos($col, '.');
                        $rel = substr($col, 0, $pos);
                        $field = substr($col, $pos + 1);
                        $w->orWhereHas($rel, fn ($x) => $x->where($field, 'like', $like));
                    } else {
                        $w->orWhere($col, 'like', $like);
                    }
                }
            });
        }

        foreach ($c['filters'] as $f) {
            $v = $request->input($f['name']);
            if ($v !== null && $v !== '') {
                $q->where($f['name'], $v);
            }
        }

        if (is_callable($c['order'])) {
            ($c['order'])($q);
        } else {
            $q->orderBy($c['order'][0], $c['order'][1] ?? 'asc');
        }

        $rows = $q->paginate(10)->withQueryString();

        return view('crud.index', compact('c', 'rows'));
    }

    public function create()
    {
        return view('crud.form', ['c' => $this->cfg(), 'model' => null]);
    }

    public function show($id)
    {
        return view('crud.show', ['c' => $this->cfg(), 'model' => $this->find($id)]);
    }

    public function edit($id)
    {
        return view('crud.form', ['c' => $this->cfg(), 'model' => $this->find($id)]);
    }

    public function store(Request $request)
    {
        $c = $this->cfg();
        $data = $request->validate($this->rules(null));
        $this->extraValidate($data, null);

        try {
            DB::transaction(function () use ($c, $data, $request) {
                $payload = $this->beforeSave($data, null, $request);
                $model = $c['model']::create($payload);
                $this->afterSave($model, $data, $request);
            });
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            report($e);

            return back()->withInput()->with('error', 'Data gagal disimpan. Periksa kembali isian Anda.');
        }

        return redirect()->route($c['route'] . '.index')->with('success', $c['title'] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $c = $this->cfg();
        $model = $this->find($id);
        $data = $request->validate($this->rules($model));
        $this->extraValidate($data, $model);

        try {
            DB::transaction(function () use ($model, $data, $request) {
                $payload = $this->beforeSave($data, $model, $request);
                $model->update($payload);
                $this->afterSave($model, $data, $request);
            });
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            report($e);

            return back()->withInput()->with('error', 'Data gagal diperbarui. Periksa kembali isian Anda.');
        }

        return redirect()->route($c['route'] . '.index')->with('success', $c['title'] . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $c = $this->cfg();
        $model = $this->find($id);

        try {
            DB::transaction(function () use ($model) {
                $this->beforeDelete($model);
                $model->delete();
                $this->afterDelete($model);
            });
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            report($e);

            return back()->with('error', 'Data tidak dapat dihapus karena masih digunakan oleh data lain.');
        }

        return redirect()->route($c['route'] . '.index')->with('success', $c['title'] . ' berhasil dihapus.');
    }
}
