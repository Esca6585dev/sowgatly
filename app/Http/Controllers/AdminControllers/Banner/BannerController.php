<?php

namespace App\Http\Controllers\AdminControllers\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\BannerRequest;
use App\Models\Banner;
use App\Models\Region;
use App\Support\ImageUploader;
use Illuminate\Http\Request;

/**
 * Admin CRUD for the home-screen promo banners (GET /api/banners).
 */
class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $banners = Banner::with('region:id,name')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($w) use ($like) {
                    foreach (Banner::fillableData() as $field) {
                        $w->orWhere($field, 'like', $like);
                    }
                });
            })
            ->orderBy('position')
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.banner.banner-table', compact('banners', 'pagination'));
        }

        return view('admin-panel.banner.banner', compact('banners', 'pagination'));
    }

    public function create($lang)
    {
        return $this->form(new Banner(['is_active' => true, 'link_type' => 'none', 'position' => 0]));
    }

    public function store($lang, BannerRequest $request)
    {
        $banner = new Banner($request->safe()->except('image'));
        $this->storeImage($banner, $request);
        $banner->save();

        return redirect()->route('banner.index', app()->getLocale())->with('success-create', 'The resource was created!');
    }

    public function show($lang, Banner $banner)
    {
        $banner->load('region:id,name');

        return view('admin-panel.banner.banner-show', compact('banner'));
    }

    public function edit($lang, Banner $banner)
    {
        return $this->form($banner);
    }

    public function update($lang, BannerRequest $request, Banner $banner)
    {
        $banner->fill($request->safe()->except('image'));
        $this->storeImage($banner, $request);
        $banner->save();

        return redirect()->route('banner.index', app()->getLocale())->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Banner $banner)
    {
        ImageUploader::delete($banner->image);
        $banner->delete();

        return redirect()->route('banner.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    private function form(Banner $banner)
    {
        $regions = Region::where('type', 'city')->orderBy('name')->get(['id', 'name']);

        return view('admin-panel.banner.banner-form', compact('banner', 'regions'));
    }

    private function storeImage(Banner $banner, BannerRequest $request): void
    {
        if ($request->hasFile('image')) {
            ImageUploader::delete($banner->image);
            $banner->image = ImageUploader::store($request->file('image'), 'banners');
        }
    }
}
