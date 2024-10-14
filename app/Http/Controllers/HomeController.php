<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cart;
use App\Models\Application;
use App\Models\Category;
use App\Models\Section;
use App\Models\Letterhead;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Auth;
use Str;
use Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'require_phone_number']);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        return view('user-panel.user-profile');
    }

    public function passwordChange()
    {
        return view('user-panel.user-profile-password-change');
    }

    public function passwordChangeEdit(Request $request)
    {
        $request->validate([
            'current_password' => ['required','string','min:8'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        $currentPasswordStatus = Hash::check($request->current_password, auth()->user()->password);

        if($currentPasswordStatus){

            $id = Auth::user()->id;

            Auth::logout();

            User::findOrFail($id)->update([
                'password' => Hash::make($request->password),
            ]);
            
            return redirect()->route('main-page', app()->getlocale() )->with('success-create','Your password has been changed!');
        } else {
            return redirect()->back()->with('warning','Current Password does not match with Old Password');
        }
    }

    public function cart(Request $request, $lang, $pagination = 5)
    {
        if($request->pagination) {
            $pagination = (int)$request->pagination;
        }

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));

                $requestData = ['number', 'name_tm', 'name_en', 'name_ru', 'name_tr'];

                $carts = Cart::with(['standart', 'user'], function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            return view('user-panel.user-cart-table', compact('carts', 'pagination'))->render();
        }

        return view('user-panel.user-cart', compact('pagination'));
    }

    public function application(Request $request, $lang, $pagination = 5)
    {
        if($request->pagination) {
            $pagination = (int)$request->pagination;
        }

        $categories = Category::where('category_id', null)->get();

        $applications = Application::orderByDesc('id')->where('user_id', Auth::id() )->withTrashed()->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));

                $requestData = ['number', 'name_tm', 'name_en', 'name_ru', 'name_tr'];

                $applications = Cart::with(['standart', 'user'], function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            return view('user-panel.user-application-table', compact('applications', 'pagination'))->render();
        }

        return view('user-panel.user-application', compact('applications', 'categories', 'pagination'));
    }

    public function applicationCreate()
    {
        $categories = Category::where('category_id', null)->get();

        $sections = Section::whereNull('section_id')->get();
        $firstSection = Section::whereNull('section_id')->first();

        $childrenSections = Section::where('section_id', $firstSection->id)->get();

        return view('user-panel.user-application-create', compact('categories', 'sections', 'childrenSections'));
    }

    public function applicationCreateSection($lang, $childrenSectionId, $slug)
    {
        $categories = Category::where('category_id', null)->get();

        $sections = Section::whereNull('section_id')->get();
        $firstSection = Section::whereNull('section_id')->first();

        $sectionId = Section::find($childrenSectionId)->parent->id;

        $childrenSections = Section::where('section_id', $sectionId)->get();

        $currentSection = Section::find($childrenSectionId);

        return view('user-panel.user-application-create-section', compact('categories', 'sections', 'sectionId', 'childrenSectionId', 'childrenSections', 'currentSection'));
    }

    public function letterhead(Request $request, $lang)
    {
        return view('user-panel.user-letterhead');
    }

    public function letterheadEdit(Request $request, $lang)
    {
        $this->addLetterHead($request, Auth::user());

        return redirect()->route('profile.letterhead', [ app()->getlocale() ])->with('success-update', 'The resource was updated!');
    }

    public function addLetterHead($request, $user)
    {
        $originalLetterhead = env('APP_URL') . '/img/Emblem_of_Turkmenistan.png';
        if($user->letterhead != null){
            $originalLetterhead = $user->letterhead->image;
        }

        if($request->file('image')){
            $this->deleteFolder($user);

            $letterheadLogo = $request->file('image');

            $date = date("d-m-Y_H-i-s");

            $code_number = Str::random(10);

            $user_name = $user->first_name . "_" . $user->last_name . "_" . $user->id;

            $fileRandName = 'logo_' . $code_number;

            $fileExt = $letterheadLogo->getClientOriginalExtension();

            $fileName = $fileRandName . '.' . $fileExt;

            $path = "assets/user/" . $user_name . '/';

            $letterheadLogo->move($path, $fileName);

            $originalLetterhead = $path . $fileName;
        }

        Letterhead::findOrCreate($user->id, [
            'user_id' => $user->id,
            'company_name_tm' => $request->company_name_tm ?? 'Edaranyň ady', 
            'company_name_en' => $request->company_name_en ?? 'Edaranyň ady',
            'address_tm' => $request->address_tm ?? 'Salgysy',
            'address_en' => $request->address_en ?? 'Salgysy',
            'phone_number_tm' => $request->phone_number_tm ?? 'Telefon belgiňiz',
            'phone_number_en' => $request->phone_number_en ?? 'Telefon belgiňiz',
            'email_tm' => $request->email_tm ?? 'Email',
            'email_en' => $request->email_en ?? 'Email',
            'image' => $originalLetterhead,
        ]);
    }
    
    public function deleteFolder($user)
    {
        if($user->letterhead){
            if($user->letterhead->image){
                $folder = explode('/', $user->letterhead->image);
    
                if($folder[2] != 'user-seeder'){
                    \File::deleteDirectory($folder[0] . '/' . $folder[1] . '/' . $folder[2]);
                }
            }
        }
    }

    public function docx($lang, $code_number)
    {
        $categories = Category::where('category_id', null)->get();

        $docx = Application::where('code_number', $code_number)->first();

        return view('user-panel.user-docx', compact('docx', 'categories'));
    }

    public function profileEdit(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->company_name = $request->company_name;
        $user->address = $request->address;
        $user->phone_number = $request->phone_number;

        $user->update();

        DB::table('model_has_roles')->where('model_id',Auth::user()->id)->delete();

        $user->assignRole($request->roles);

        return __('The resource was updated!');
    }

    public function email()
    {
        $categories = Category::where('category_id', null)->get();

        $application = Application::findOrFail(70);

        $user = User::findOrFail($application->user_id);

        return view('emails.application-tds', compact('application', 'user', 'categories'));
    }
}
