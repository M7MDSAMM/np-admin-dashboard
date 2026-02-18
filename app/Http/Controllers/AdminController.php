<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\AdminManagementServiceInterface;
use App\Services\Exceptions\ExternalServiceException;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminManagementServiceInterface $adminService,
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function index(Request $request): View
    {
        $page   = max(1, (int) $request->input('page', 1));
        $search = $request->input('search', '');

        try {
            $result     = $this->adminService->listAdmins($this->auth->getToken(), $page);
            $admins     = $result['data'];
            $pagination = $result['pagination'];
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException) {
            $admins     = [];
            $pagination = null;
            session()->flash('error', 'Failed to load admin list from User Service.');
        }

        // Client-side search filter (filters the current page of results).
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

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'string', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'string', 'in:super_admin,admin'],
        ]);

        unset($data['password_confirmation']);

        try {
            $admin = $this->adminService->createAdmin($this->auth->getToken(), $data);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Admin created successfully.', 'data' => $admin]);
            }

            return redirect()->route('admins.index')->with('success', 'Admin created successfully.');
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->context], 422);
            }

            return back()->withInput()
                ->with('error', $e->getMessage())
                ->withErrors($e->context);
        }
    }

    public function edit(string $uuid): View|RedirectResponse
    {
        try {
            $admin = $this->adminService->findAdmin($this->auth->getToken(), $uuid);
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException) {
            return redirect()->route('admins.index')->with('error', 'Failed to load admin details.');
        }

        if (! $admin) {
            return redirect()->route('admins.index')->with('error', 'Admin not found.');
        }

        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, string $uuid): JsonResponse|RedirectResponse
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
            $admin = $this->adminService->updateAdmin($this->auth->getToken(), $uuid, $data);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Admin updated successfully.', 'data' => $admin]);
            }

            return redirect()->route('admins.index')->with('success', 'Admin updated successfully.');
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->context], 422);
            }

            return back()->withInput()
                ->with('error', $e->getMessage())
                ->withErrors($e->context);
        }
    }

    public function destroy(Request $request, string $uuid): JsonResponse|RedirectResponse
    {
        try {
            $this->adminService->deleteAdmin($this->auth->getToken(), $uuid);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Admin deleted successfully.']);
            }

            return redirect()->route('admins.index')->with('success', 'Admin deleted successfully.');
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->route('admins.index')->with('error', 'Failed to delete admin.');
        }
    }

    public function toggleActive(Request $request, string $uuid): JsonResponse|RedirectResponse
    {
        try {
            $admin  = $this->adminService->toggleActive($this->auth->getToken(), $uuid);
            $status = ($admin['is_active'] ?? false) ? 'activated' : 'deactivated';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => "Admin {$status} successfully.", 'data' => $admin]);
            }

            return redirect()->route('admins.index')->with('success', "Admin {$status} successfully.");
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to toggle admin status.'], 500);
            }

            return redirect()->route('admins.index')->with('error', 'Failed to toggle admin status.');
        }
    }
}
