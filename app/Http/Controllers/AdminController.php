<?php

namespace App\Http\Controllers;

use App\Application\Admin\AdminManagementService;
use App\Application\Auth\AdminSessionService;
use App\Domain\Exceptions\ExternalServiceException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminManagementService $adminService,
        private readonly AdminSessionService $sessionService,
    ) {}

    public function index(Request $request): View
    {
        $page = max(1, (int) $request->input('page', 1));
        $search = $request->input('search', '');

        try {
            $result = $this->adminService->listAdmins($this->sessionService->getToken(), $page);
            $admins = $result['data'];
            $pagination = $result['pagination'];
        } catch (ExternalServiceException) {
            $admins = [];
            $pagination = null;
            session()->flash('error', 'Failed to load admin list from User Service.');
        }

        if ($search !== '') {
            $admins = array_values(array_filter($admins, function (array $admin) use ($search) {
                return str_contains(strtolower($admin['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($admin['email'] ?? ''), strtolower($search));
            }));
        }

        return view('admins.index', compact('admins', 'search', 'pagination'));
    }

    public function create(): View
    {
        return view('admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'string', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'string', 'in:super_admin,admin'],
        ]);

        unset($data['password_confirmation']);

        try {
            $this->adminService->createAdmin($this->sessionService->getToken(), $data);

            return redirect()->route('admins.index')->with('success', 'Admin created successfully.');
        } catch (ExternalServiceException $e) {
            return back()->withInput()
                ->with('error', $e->getMessage())
                ->withErrors($e->context);
        }
    }

    public function edit(string $uuid): View|RedirectResponse
    {
        try {
            $admin = $this->adminService->findAdmin($this->sessionService->getToken(), $uuid);
        } catch (ExternalServiceException) {
            return redirect()->route('admins.index')->with('error', 'Failed to load admin details.');
        }

        if (! $admin) {
            return redirect()->route('admins.index')->with('error', 'Admin not found.');
        }

        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, string $uuid): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'string', 'email', 'max:190'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'string', 'in:super_admin,admin'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }
        unset($data['password_confirmation']);

        try {
            $this->adminService->updateAdmin($this->sessionService->getToken(), $uuid, $data);

            return redirect()->route('admins.index')->with('success', 'Admin updated successfully.');
        } catch (ExternalServiceException $e) {
            return back()->withInput()
                ->with('error', $e->getMessage())
                ->withErrors($e->context);
        }
    }

    public function destroy(string $uuid): RedirectResponse
    {
        try {
            $this->adminService->deleteAdmin($this->sessionService->getToken(), $uuid);

            return redirect()->route('admins.index')->with('success', 'Admin deleted successfully.');
        } catch (ExternalServiceException) {
            return redirect()->route('admins.index')->with('error', 'Failed to delete admin.');
        }
    }

    public function toggleActive(string $uuid): RedirectResponse
    {
        try {
            $admin = $this->adminService->toggleActive($this->sessionService->getToken(), $uuid);
            $status = ($admin['is_active'] ?? false) ? 'activated' : 'deactivated';

            return redirect()->route('admins.index')->with('success', "Admin {$status} successfully.");
        } catch (ExternalServiceException) {
            return redirect()->route('admins.index')->with('error', 'Failed to toggle admin status.');
        }
    }
}
