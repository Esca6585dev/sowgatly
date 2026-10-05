<?php

namespace App\Http\Controllers\AdminControllers\Region;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegionRequest;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public const TYPES = ['country', 'province', 'city', 'village'];

    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $regions = Region::with('parent:id,name')
            ->withCount(['children', 'shops'])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when(in_array($request->input('type'), self::TYPES, true), fn ($q) => $q->where('type', $request->input('type')))
            ->orderBy('type')->orderBy('name')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.region.region-table', compact('regions', 'pagination'));
        }

        return view('admin-panel.region.region', compact('regions', 'pagination'));
    }

    public function create($lang)
    {
        return $this->form(new Region(['type' => 'city']));
    }

    public function store($lang, RegionRequest $request)
    {
        $region = Region::create($request->validated());

        return redirect()->route('region.show', [app()->getLocale(), $region->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, Region $region)
    {
        $region->load('parent', 'children', 'shops:id,name,region_id,status');

        return view('admin-panel.region.region-show', compact('region'));
    }

    public function edit($lang, Region $region)
    {
        return $this->form($region);
    }

    public function update($lang, RegionRequest $request, Region $region)
    {
        $region->update($request->validated());

        return redirect()->route('region.show', [app()->getLocale(), $region->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Region $region)
    {
        $region->delete();

        return redirect()->route('region.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    private function form(Region $region)
    {
        $parents = Region::where('id', '!=', $region->id ?? 0)
            ->whereIn('type', ['country', 'province', 'city'])
            ->orderBy('type')->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('admin-panel.region.region-form', compact('region', 'parents'));
    }
}
