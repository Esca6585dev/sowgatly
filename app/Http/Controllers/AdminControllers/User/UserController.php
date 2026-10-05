<?php

namespace App\Http\Controllers\AdminControllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\ImageUploader;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * Users matching the list filters (search, status, has_shop); shared by
     * the index page and the export.
     */
    private function filtered(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $digits = preg_replace('/\D+/', '', $search);
        $status = $request->input('status');
        $hasShop = $request->input('has_shop');

        return User::with('shop:id,user_id,name')
            ->withCount('orders')
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search, $digits) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                if ($digits !== '') {
                    // "+993 65 12 34 56" and "65123456" both find 65123456.
                    $q->orWhere('phone_number', 'like', '%' . (strlen($digits) > 8 ? substr($digits, -8) : $digits) . '%');
                }
            }))
            ->when(in_array($status, ['0', '1'], true), fn ($q) => $q->where('status', (int) $status))
            ->when($hasShop === '1', fn ($q) => $q->has('shop'))
            ->when($hasShop === '0', fn ($q) => $q->doesntHave('shop'));
    }

    /**
     * Download the filtered user list as CSV (UTF-8 with BOM so Excel shows
     * Turkmen letters correctly).
     */
    public function export(Request $request, $lang)
    {
        $file = 'sowgatly-users-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', __('Name'), __('Phone number'), __('Email'), __('Birth date'), __('Shop'), __('Orders'), __('Status'), __('Registered')], ';');
            $this->filtered($request)->orderBy('id')->chunk(500, function ($users) use ($out) {
                foreach ($users as $u) {
                    fputcsv($out, [
                        $u->id, $u->name, $u->phone_number ? '+993 ' . $u->phone_number : '', $u->email,
                        optional($u->birth_date)->format('Y-m-d'), optional($u->shop)->name, $u->orders_count,
                        $u->status ? __('Active') : __('Inactive'), optional($u->created_at)->format('Y-m-d H:i'),
                    ], ';');
                }
            });
            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;

        $users = $this->filtered($request)
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.user.user-table', compact('users', 'pagination'));
        }

        return view('admin-panel.user.user', compact('users', 'pagination'));
    }

    public function create($lang)
    {
        $user = new User;
        $user->status = 1; // not mass assignable

        return view('admin-panel.user.user-form', compact('user'));
    }

    public function store($lang, UserRequest $request)
    {
        $user = new User;
        $this->fill($user, $request);
        $user->save();

        return redirect()->route('user.show', [app()->getLocale(), $user->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, User $user)
    {
        $user->load([
            'shop:id,user_id,name,image,status',
            'deliveryAddresses' => fn ($q) => $q->orderByDesc('is_default')->orderBy('id'),
            'orders' => fn ($q) => $q->with('shop:id,name')->latest()->limit(8),
        ])->loadCount(['orders', 'favorites']);

        $totalSpent = $user->orders()->where('status', '!=', 'cancelled')->sum('total_amount');

        return view('admin-panel.user.user-show', compact('user', 'totalSpent'));
    }

    public function edit($lang, User $user)
    {
        return view('admin-panel.user.user-form', compact('user'));
    }

    public function update($lang, UserRequest $request, User $user)
    {
        $this->fill($user, $request);
        $user->save();

        return redirect()->route('user.show', [app()->getLocale(), $user->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, User $user)
    {
        // Deleting a user cascades to their shop and its products; make that a deliberate step.
        if ($user->shop()->exists()) {
            return redirect()->route('user.show', [app()->getLocale(), $user->id])
                ->with('warning', 'This user owns a shop. Delete the shop first.');
        }

        ImageUploader::delete($user->image);
        $user->delete();

        return redirect()->route('user.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    /** Copy the validated fields onto the user. Customers sign in by OTP, so the password is never touched here. */
    private function fill(User $user, UserRequest $request): void
    {
        $data = $request->validated();

        $user->name = $data['name'];
        $user->phone_number = $data['phone_number'];
        $user->email = $data['email'] ?? null;
        $user->birth_date = $data['birth_date'] ?? null;
        $user->status = (bool) ($data['status'] ?? false);

        if ($request->hasFile('image')) {
            $old = $user->image;
            $user->image = ImageUploader::store($request->file('image'), 'users');
            ImageUploader::delete($old);
        }
    }
}
