<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Http\Requests\Super\StoreOrganizationRequest;
use App\Http\Requests\Super\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Services\Super\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationService $service) {}

    public function index(): View
    {
        $organizations = Organization::orderBy('created_at', 'desc')->get();
        return view('super.organizations.index', compact('organizations'));
    }

    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('super.organizations.index');
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $this->service->update($organization, $request->validated());
        return redirect()->route('super.organizations.index');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $this->service->delete($organization);
        return redirect()->route('super.organizations.index');
    }
}
