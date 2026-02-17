<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\UserManagementServiceInterface;
use App\Services\Exceptions\ExternalServiceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDevicesController extends Controller
{
    public function __construct(
        private readonly UserManagementServiceInterface $userService,
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function index(string $uuid): View|RedirectResponse
    {
        try {
            $user    = $this->userService->getUser($this->auth->getToken(), $uuid);
            $devices = $this->userService->listDevices($this->auth->getToken(), $uuid);
        } catch (ExternalServiceException) {
            return redirect()->route('users.index')->with('error', 'Failed to load devices.');
        }

        if (! $user) {
            return redirect()->route('users.index')->with('error', 'User not found.');
        }

        return view('users.devices', compact('user', 'devices'));
    }

    public function store(Request $request, string $uuid): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'token'    => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
        ]);

        try {
            $this->userService->addDevice($this->auth->getToken(), $uuid, $data);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Device added successfully.']);
            }

            return redirect()->route('users.devices', $uuid)->with('success', 'Device added successfully.');
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->context], 422);
            }

            return back()->with('error', $e->getMessage())->withErrors($e->context);
        }
    }

    public function destroy(Request $request, string $uuid, string $deviceUuid): JsonResponse|RedirectResponse
    {
        try {
            $this->userService->deleteDevice($this->auth->getToken(), $uuid, $deviceUuid);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Device removed successfully.']);
            }

            return redirect()->route('users.devices', $uuid)->with('success', 'Device removed successfully.');
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->route('users.devices', $uuid)->with('error', 'Failed to remove device.');
        }
    }
}
