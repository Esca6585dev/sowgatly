<?php

namespace App\Http\Controllers\AdminControllers\ShopApplication;

use App\Http\Controllers\Controller;
use App\Models\ShopApplication;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class ShopApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $applications = ShopApplication::with('user:id,name,phone_number', 'region:id,name')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('name', 'like', $like)->orWhere('phone', 'like', $like);
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.shop-application.shop-application-table', compact('applications', 'pagination'))->render();
        }

        return view('admin-panel.shop-application.shop-application', compact('applications', 'pagination'));
    }

    public function show($lang, ShopApplication $shop_application)
    {
        $shop_application->load('user', 'region');

        return view('admin-panel.shop-application.shop-application-show', ['application' => $shop_application]);
    }

    public function update(Request $request, $lang, ShopApplication $shop_application)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', ShopApplication::STATUSES),
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $changed = $data['status'] !== $shop_application->status;
        $shop_application->update($data);

        // The applicant hears about a decision in the app when they are a user.
        if ($changed && $shop_application->user_id && in_array($data['status'], ['approved', 'rejected'], true)) {
            UserNotification::create([
                'user_id' => $shop_application->user_id,
                'type' => 'shop_application',
                'data' => ['application_id' => $shop_application->id, 'status' => $data['status']],
            ]);
        }

        return redirect()->route('shop-application.show', [app()->getLocale(), $shop_application->id])->with('success-update', 'The resource was updated!');
    }
}
