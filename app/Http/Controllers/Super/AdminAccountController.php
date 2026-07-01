<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Http\Requests\Super\StoreAdminAccountRequest;
use App\Http\Requests\Super\UpdateAdminAccountRequest;
use App\Models\Admin;
use App\Models\Organization;
use App\Services\Super\AdminAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function __construct(private AdminAccountService $service) {}

    public function index(): View
    {
        $admins        = Admin::where('is_super', false)->with('organization')->orderBy('created_at', 'desc')->get();
        $organizations = Organization::orderBy('name')->get();
        return view('super.admins.index', compact('admins', 'organizations'));
    }

    public function store(StoreAdminAccountRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('super.admins.index');
    }

    public function update(UpdateAdminAccountRequest $request, Admin $admin): RedirectResponse
    {
        $this->service->update($admin, $request->validated());
        return redirect()->route('super.admins.index');
    }

    public function toggleActive(Request $request, Admin $admin): RedirectResponse
    {
        $this->service->toggleActive($admin, (bool) $request->input('is_active'));
        return redirect()->route('super.admins.index');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        $this->service->delete($admin);
        return redirect()->route('super.admins.index');
    }
}
