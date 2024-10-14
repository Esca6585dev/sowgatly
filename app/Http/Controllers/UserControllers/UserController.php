<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use SapientPro\ImageComparatorLaravel\Facades\Comparator;
use SapientPro\ImageComparator\Strategy\DifferenceHashStrategy;
use SapientPro\ImageComparator\ImageComparator;
use Str;
use Auth;

class UserController extends Controller
{
    public function goToMainPage(Request $request)
    {
        $location = Location::get($request->ip());

        if($location){
            if (in_array(Str::lower($location->countryCode), config('app.locales'))) {
                return redirect()->route('main-page', Str::lower($location->countryCode));
            }
        }

        return redirect()->route('main-page', 'tm');
    }
    
    public function mainPage()
    {
        return view('user-panel.main-page');
    }

    public function resume()
    {
        return view('resume');
    }
}
