<?php

namespace App\Http\Controllers\AdminControllers\Region;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Seller;
use App\Http\Requests\RegionRequest;
use Str;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $lang, $pagination = 10)
    {
        if($request->pagination) {
            $pagination = (int)$request->pagination;
        }

        $regions = Region::orderByDesc('id')->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = Region::fillableData();
    
                $regions = Region::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.region.region-table', compact('regions', 'pagination'))->render();
        }

        return view('admin-panel.region.region', compact('regions', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, Region $region)
    {
        $sellers = Seller::all();

        return view('admin-panel.region.region-form', compact('region', 'sellers'));
    }

    /**
 * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($lang, RegionRequest $request)
    {   
        $region = new Region;

        $this->uploadImage($region, $request);
        
        $region->name = $request->name;
        $region->email = $request->email;
        $region->address = $request->address;
        $region->mon_fri_open = $request->mon_fri_open;
        $region->mon_fri_close = $request->mon_fri_close;
        $region->sat_sun_open = $request->sat_sun_open;
        $region->sat_sun_close = $request->sat_sun_close;
        $region->seller_id = $request->seller_id;

        $region->save();

        return redirect()->route('region.index', app()->getlocale() )->with('success-create', 'The resource was created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function show($lang, Region $region)
    {
        $sellers = Seller::all();

        return view('admin-panel.region.region-form', compact('region', 'sellers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, Region $region)
    {
        $sellers = Seller::all();

        return view('admin-panel.region.region-form', compact('region', 'sellers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function update($lang, RegionRequest $request, Region $region)
    {
        $this->uploadImage($region, $request);
        
        $region->name = $request->name;
        $region->email = $request->email;
        $region->address = $request->address;
        $region->mon_fri_open = $request->mon_fri_open;
        $region->mon_fri_close = $request->mon_fri_close;
        $region->sat_sun_open = $request->sat_sun_open;
        $region->sat_sun_close = $request->sat_sun_close;
        $region->seller_id = $request->seller_id;

        $region->update();

        return redirect()->route('region.index', app()->getlocale() )->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, Region $region)
    {
        $this->deleteFolder($region);

        $region->delete();

        return redirect()->route('region.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }

    public function deleteFolder($region)
    {
        if($region->image){
            $folder = explode('/', $region->image);

            if($folder[1] != 'region-seeder'){
                \File::deleteDirectory($folder[0] . '/' . $folder[1]);
            }
        }
    }

    public function uploadImage($region, $request)
    {
        if($request->file('image')){
            $this->deleteFolder($region);

            $image = $request->file('image');

            $date = date("d-m-Y H-i-s");
            
            $fileRandName = Str::random(10);
            $fileExt = $image->getClientOriginalExtension();

            $fileName = $fileRandName . '.' . $fileExt;
            
            $path = 'region/' . Str::slug($request->name . '-' . $date ) . '/';

            $image->move($path, $fileName);
            
            $originalImage = $path . $fileName;

            $region->image = $originalImage;
        }
    }
}
