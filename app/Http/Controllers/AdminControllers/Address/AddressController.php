<?php

namespace App\Http\Controllers\AdminControllers\Address;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Seller;
use App\Http\Requests\AddressRequest;
use Str;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang, $pagination = 10)
    {
        if($request->pagination) {
            $pagination = (int)$request->pagination;
        }

        $addresses = Address::orderByDesc('id')->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = ['street', 'city', 'state', 'country', 'postal_code'];
    
                $addresses = Address::where(function($q) use($requestData, $searchQuery) {
                    foreach ($requestData as $field)
                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                })->paginate($pagination);
            }
            
            return view('admin-panel.address.address-table', compact('addresses', 'pagination'))->render();
        }

        return view('admin-panel.address.address', compact('addresses', 'pagination'));
    }

    public function create($lang, Address $address)
    {
        $sellers = Seller::all();

        return view('admin-panel.address.address-form', compact('address', 'sellers'));
    }

    public function store($lang, AddressRequest $request)
    {   
        $address = new Address;
        
        $address->street = $request->street;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->country = $request->country;
        $address->postal_code = $request->postal_code;
        $address->seller_id = $request->seller_id;

        $address->save();

        return redirect()->route('address.index', app()->getlocale())->with('success-create', 'The address was created!');
    }

    public function show($lang, Address $address)
    {
        $sellers = Seller::all();

        return view('admin-panel.address.address-form', compact('address', 'sellers'));
    }

    public function edit($lang, Address $address)
    {
        $sellers = Seller::all();

        return view('admin-panel.address.address-form', compact('address', 'sellers'));
    }

    public function update($lang, AddressRequest $request, Address $address)
    {
        $address->street = $request->street;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->country = $request->country;
        $address->postal_code = $request->postal_code;
        $address->seller_id = $request->seller_id;

        $address->update();

        return redirect()->route('address.index', app()->getlocale())->with('success-update', 'The address was updated!');
    }

    public function destroy($lang, Address $address)
    {
        $address->delete();

        return redirect()->route('address.index', [app()->getlocale()])->with('success-delete', 'The address was deleted!');
    }
}