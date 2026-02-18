<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\UserManagementServiceInterface;
use App\Services\Exceptions\ExternalServiceException;
use App\Services\Exceptions\UnauthorizedRemoteException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function __construct(
        private readonly UserManagementServiceInterface $userService,
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function index(Request $request): View
    {
        $query = array_filter([
            'page'      => max(1, (int) $request->input('page', 1)),
            'per_page'  => 15,
            'email'     => $request->input('email', '') ?: null,
            'is_active' => $request->has('is_active') ? $request->input('is_active') : null,
        ], fn ($v) => $v !== null);

        try {
            $result     = $this->userService->paginateUsers($this->auth->getToken(), $query);
            $users      = $result['data'];
            $pagination = $result['pagination'];
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException) {
            $users      = [];
            $pagination = null;
            session()->flash('error', 'Failed to load users from User Service.');
        }

        $email = $request->input('email', '');

        return view('users.index', compact('users', 'pagination', 'email'));
    }

    public function show(string $uuid): View|RedirectResponse
    {
        try {
            $user = $this->userService->getUser($this->auth->getToken(), $uuid);
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException) {
            return redirect()->route('users.index')->with('error', 'Failed to load user.');
        }

        if (! $user) {
            return redirect()->route('users.index')->with('error', 'User not found.');
        }

        return view('users.show', compact('user'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'email'      => ['required', 'string', 'email', 'max:190'],
            'phone_e164' => ['nullable', 'string', 'max:25'],
            'locale'     => ['sometimes', 'string', 'max:10'],
            'timezone'   => ['nullable', 'string', 'max:50'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        try {
            $this->userService->createUser($this->auth->getToken(), $data);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'User created successfully.']);
            }

            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->context], 422);
            }

            return back()->withInput()->with('error', $e->getMessage())->withErrors($e->context);
        }
    }

    public function edit(string $uuid): View|RedirectResponse
    {
        try {
            $user = $this->userService->getUser($this->auth->getToken(), $uuid);
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException) {
            return redirect()->route('users.index')->with('error', 'Failed to load user.');
        }

        if (! $user) {
            return redirect()->route('users.index')->with('error', 'User not found.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, string $uuid): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'email'      => ['required', 'string', 'email', 'max:190'],
            'phone_e164' => ['nullable', 'string', 'max:25'],
            'locale'     => ['sometimes', 'string', 'max:10'],
            'timezone'   => ['nullable', 'string', 'max:50'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        try {
            $this->userService->updateUser($this->auth->getToken(), $uuid, $data);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'User updated successfully.']);
            }

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->context], 422);
            }

            return back()->withInput()->with('error', $e->getMessage())->withErrors($e->context);
        }
    }

    public function destroy(Request $request, string $uuid): JsonResponse|RedirectResponse
    {
        try {
            $this->userService->deleteUser($this->auth->getToken(), $uuid);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
            }

            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->route('users.index')->with('error', 'Failed to delete user.');
        }
    }
}
