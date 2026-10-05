# Admin panel — how pages are built (new design, no Metronic)

Reference implementation: **Regions** — `app/Http/Controllers/AdminControllers/Region/RegionController.php`,
`app/Http/Requests/RegionRequest.php`, `resources/views/admin-panel/region/*`,
`tests/Feature/Admin/RegionAdminTest.php`. Dashboard: `admin-panel/dashboard/dashboard.blade.php`.
Copy their structure.

## Files

- Layout: every page `@extends('layouts.admin-page')` and fills `page-title` (plain text),
  `breadcrumb` (e.g. `<a href="…">Regions</a><span class="sep">/</span><span>Edit</span>`) and `content`.
  The layout already renders flash toasts, validation toast, the confirm dialog, sidebar and top bar —
  pages must NOT include `layouts.alert`, `layouts.header`, `layouts.sidebar`, Metronic CSS/JS or jQuery.
- CSS/JS: `public/admin/admin.css` and `public/admin/admin.js` only. Use the classes below; no inline
  `<style>` blocks, no Bootstrap classes (`row`, `col-lg-*`, `form-control`, `btn-primary` from bootstrap,
  `badge-*`, `card-custom`, `d-flex`…). Small inline `style=""` for one-off spacing is fine.
- Per section: `{name}.blade.php` (index), `{name}-table.blade.php` (the AJAX partial: table + `->links('layouts.pagination')`,
  no wrapper div), `{name}-form.blade.php` (create + edit), `{name}-show.blade.php` (detail). Delete the old
  `{name}-action.blade.php` partials; use `<x-admin.row-actions>` instead.

## Components (`resources/views/components/admin/`)

| Component | Use |
|---|---|
| `<x-admin.page-header :title :subtitle>` + `<x-slot:actions>` | Page title row with buttons |
| `<x-admin.card :title :subtitle flush>` + `<x-slot:right>` / `<x-slot:footer>` | Card. `flush` = no padding (tables, lists) |
| `<x-admin.toolbar :placeholder :per-page="$pagination">` + extra `<select>` filters in slot | Search box + filters + page size. Drives `#datatable` via AJAX |
| `<x-admin.row-actions route="region" :model="$id" :params="[]" :show :edit :delete>` | Eye / pencil / trash (trash asks for confirmation) |
| `<x-admin.form :action :method :files>` | Form with CSRF and method spoofing |
| `<x-admin.field name label :value type col hint addon>` | Input (+ error). `col` = `col-12/8/6/4/3` inside `.form-grid` |
| `<x-admin.select name label :options :value :placeholder col>` | Select (`:options` = [value => label]; `multiple` + `name="x[]"` works) |
| `<x-admin.textarea name label :value col>` | Textarea |
| `<x-admin.checkbox name label :checked switch>` | Checkbox / switch; sends 0 when unchecked |
| `<x-admin.file name label :multiple :current="[urls]">` | Image upload with previews |
| `<x-admin.status :value>` | Coloured pill for statuses (pending, approved, paid, 1/0 …) |
| `<x-admin.pill tone="ok|warn|bad|info|violet|brand" dot>` | Any small label |
| `<x-admin.stat icon label value hero trend foot href>` | KPI tile |
| `<x-admin.empty icon text>` | Empty state |
| `<x-admin.icon name class="i-sm">` | Icons: see the `$paths` keys in `icon.blade.php` |

Useful classes: `.grid .grid-2/3/4`, `.split` (main + 340px side), `.stack`, `.form-grid` + `.col-*`,
`.form-section` (heading inside a form grid), `.tbl` (inside `.table-wrap`), `.right`, `.num`, `.nowrap`,
`.muted`, `.small`, `.who` (avatar + name), `.avatar`, `.thumb`, `.thumb.lg`, `.list` / `.list-item`,
`.dl` (definition list: `<dl class="dl"><dt>…</dt><dd>…</dd></dl>`), `.chips/.chip.on`, `.btn`, `.btn-primary`,
`.btn-soft`, `.btn-danger`, `.btn-ghost`, `.btn-sm`, `.checks` (grid of checkboxes), `.previews`, `.bubbles/.bubble.mine`.

## Controllers

- Index: `$pagination = (int) $request->input('pagination', 10) ?: 10;` search with `when()`, filters from the
  query string, `->paginate($pagination)->withQueryString()`. If `$request->ajax()` return the `-table` view,
  otherwise the full page. Eager-load what the table shows.
- Store/update through a FormRequest whose rules match the **real** columns of the model/migration
  (many existing controllers and requests were copy-pasted from Shop and reference columns or classes —
  `Seller`, `address`, `mon_fri_open` — that do not exist on that model; fix them).
- After store/update redirect to the show page (or index) with `->with('success-create'|'success-update', 'The resource was created!'|'The resource was updated!')`;
  after destroy `->with('success-delete', 'The resource was deleted!')`.
- Images: store on the public disk (`$file->store('dir', 'public')`) and save the path the existing models/
  resources expect — check how the API resources build the URL before changing a stored path format.
- Keep route names and URLs as they are (`route('x.index', [app()->getLocale(), ...])`).

## Text

- All visible text through `__('English text')`. Do **not** edit `resources/lang/*.json`; the lead adds Turkmen
  and Russian translations for every new key at the end.

## Tests

- Extend `Tests\Feature\Admin\AdminTestCase` (signed-in admin, `$this->adminUrl('path')`).
- One test file per section: index (+ AJAX search returns the partial), create form, store (valid + invalid),
  show, edit, update, destroy. Run: `php artisan test tests/Feature/Admin/YourTest.php`.

## Visual check

A dev server runs at `http://127.0.0.1:8000` on a seeded SQLite database (admin `admin-sowgatly` /
`password-sowgatly`). Screenshot pages with
`node /tmp/claude-0/-home-user/ffa73d88-a415-52c3-be72-0112543c0e54/scratchpad/shots/admin.js <outdir> name=/tm/admin/region@light name2=/tm/admin/region/1/edit@dark`
(options after the URL: `@theme@collapsed(0/1)@width@full`) and look at the PNGs.
