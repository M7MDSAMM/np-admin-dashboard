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

class UserPreferencesController extends Controller
{
    public function __construct(
        private readonly UserManagementServiceInterface $userService,
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function show(string $uuid): View|RedirectResponse
    {
        try {
            $user  = $this->userService->getUser($this->auth->getToken(), $uuid);
            $prefs = $this->userService->getPreferences($this->auth->getToken(), $uuid);
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException) {
            return redirect()->route('users.index')->with('error', 'Failed to load preferences.');
        }

        if (! $user) {
            return redirect()->route('users.index')->with('error', 'User not found.');
        }

        return view('users.preferences', compact('user', 'prefs'));
    }

    public function update(Request $request, string $uuid): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'channel_email'        => ['sometimes', 'boolean'],
            'channel_whatsapp'     => ['sometimes', 'boolean'],
            'channel_push'         => ['sometimes', 'boolean'],
            'rate_limit_per_minute' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'quiet_hours_start'    => ['nullable', 'date_format:H:i'],
            'quiet_hours_end'      => ['nullable', 'date_format:H:i'],
        ]);

        // Checkboxes not sent when unchecked — normalize to false
        foreach (['channel_email', 'channel_whatsapp', 'channel_push'] as $ch) {
            $data[$ch] = $request->boolean($ch);
        }

        try {
            $this->userService->updatePreferences($this->auth->getToken(), $uuid, $data);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Preferences updated.']);
            }

            return redirect()->route('users.preferences', $uuid)->with('success', 'Preferences updated.');
        } catch (UnauthorizedRemoteException $e) {
            throw $e;
        } catch (ExternalServiceException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->context], 422);
            }

            return back()->with('error', $e->getMessage())->withErrors($e->context);
        }
    }
}
