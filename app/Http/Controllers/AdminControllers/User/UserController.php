<?php

namespace App\Http\Controllers\AdminControllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use Str;

class UserController extends Controller
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

        $users = User::orderByDesc('id')->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = User::fillableData();
    
                $users = User::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.user.user-table', compact('users', 'pagination'))->render();
        }

        return view('admin-panel.user.user', compact('users', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, User $user)
    {
        return view('admin-panel.user.user-form', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($lang, UserRequest $request)
    {   
        $user = new User;

        $this->uploadImage($user, $request);
        
        $user->name = $request->name;
        $user->phone_number = $request->phone_number;
        $user->status = $request->status;
        $user->password = Hash::make($request->password);

        $user->save();

        return redirect()->route('user.index', app()->getlocale() )->with('success-create', 'The resource was created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show($lang, User $user)
    {
        return view('admin-panel.user.user-show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, User $user)
    {
        return view('admin-panel.user.user-form', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update($lang, UserRequest $request, User $user)
    {
        $this->uploadImage($user, $request);
        
        $user->name = $request->name;
        $user->phone_number = $request->phone_number;
        $user->status = $request->status;
        $user->password = Hash::make($request->password);

        $user->update();

        return redirect()->route('user.index', app()->getlocale() )->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, User $user)
    {
        $this->deleteFolder($user);

        $user->delete();

        return redirect()->route('user.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }

    public function deleteFolder($user)
    {
        if($user->image){
            $folder = explode('/', $user->image);

            if($folder[1] != 'user-seeder'){
                \File::deleteDirectory($folder[0] . '/' . $folder[1]);
            }
        }
    }

    public function uploadImage($user, $request)
    {
        if($request->file('image')){
            $this->deleteFolder($user);

            $image = $request->file('image');

            $date = date("d-m-Y H-i-s");
            
            $fileRandName = Str::random(10);
            $fileExt = $image->getClientOriginalExtension();

            $fileName = $fileRandName . '.' . $fileExt;
            
            $path = 'user/' . Str::slug($request->name . '-' . $date ) . '/';

            $image->move($path, $fileName);
            
            $originalImage = $path . $fileName;

            $user->image = $originalImage;
        }
    }
}
