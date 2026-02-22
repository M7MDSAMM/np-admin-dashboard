<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationCreateRequest;
use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\NotificationManagementServiceInterface;
use App\Services\Exceptions\ExternalServiceException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationManagementServiceInterface $notifications,
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function index(): View
    {
        return view('notifications.index', [
            'lastNotification' => session('last_notification'),
            'currentAdmin'     => $this->auth->getAdmin(),
        ]);
    }

    public function create(): View
    {
        return view('notifications.create', [
            'currentAdmin' => $this->auth->getAdmin(),
        ]);
    }

    public function store(NotificationCreateRequest $request): RedirectResponse
    {
        try {
            $result = $this->notifications->createNotification($request->validated());
            $notification = $result['data'] ?? [];

            session()->flash('last_notification', $notification);

            return redirect()
                ->route('notifications.show', $notification['uuid'] ?? '')
                ->with('success', $result['message'] ?? 'Notification created.')
                ->with('correlation_id', $result['correlation_id'] ?? null);
        } catch (ExternalServiceException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage())
                ->with('error_code', $e->errorCode)
                ->withErrors($e->context);
        }
    }

    public function show(string $uuid): View|RedirectResponse
    {
        try {
            $result = $this->notifications->getNotification($uuid);
            $notification = $result['data'] ?? [];

            return view('notifications.show', [
                'notification'   => $notification,
                'correlationId'  => $result['correlation_id'] ?? null,
                'currentAdmin'   => $this->auth->getAdmin(),
            ]);
        } catch (ExternalServiceException $e) {
            return redirect()
                ->route('notifications.index')
                ->with('error', $e->getMessage())
                ->with('error_code', $e->errorCode);
        }
    }

    public function retry(string $uuid): RedirectResponse
    {
        try {
            $result = $this->notifications->retryNotification($uuid);

            return redirect()
                ->route('notifications.show', $uuid)
                ->with('success', $result['message'] ?? 'Notification retry accepted.')
                ->with('correlation_id', $result['correlation_id'] ?? null);
        } catch (ExternalServiceException $e) {
            return redirect()
                ->route('notifications.show', $uuid)
                ->with('error', $e->getMessage())
                ->with('error_code', $e->errorCode);
        }
    }
}
