<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationCreateRequest;
use App\Services\Contracts\AdminAuthServiceInterface;
use App\Services\Contracts\NotificationManagementServiceInterface;
use App\Services\Exceptions\ExternalServiceException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationManagementServiceInterface $notifications,
        private readonly AdminAuthServiceInterface $auth,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $filters = $request->only(['status', 'user_uuid', 'template_key']);
            $page = (int) $request->query('page', 1);

            $result = $this->notifications->listNotifications($filters, $page);
            $data = $result['data'] ?? [];

            return view('notifications.index', [
                'notifications' => $data['data'] ?? [],
                'pagination'    => [
                    'current_page' => $data['current_page'] ?? 1,
                    'last_page'    => $data['last_page'] ?? 1,
                    'from'         => $data['from'] ?? 0,
                    'to'           => $data['to'] ?? 0,
                    'total'        => $data['total'] ?? 0,
                ],
                'filters'      => $filters,
                'currentAdmin' => $this->auth->getAdmin(),
            ]);
        } catch (ExternalServiceException $e) {
            return view('notifications.index', [
                'notifications' => [],
                'pagination'    => null,
                'filters'       => $request->only(['status', 'user_uuid', 'template_key']),
                'currentAdmin'  => $this->auth->getAdmin(),
                'error'         => $e->getMessage(),
            ]);
        }
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
                'notification'  => $notification,
                'correlationId' => $result['correlation_id'] ?? null,
                'currentAdmin'  => $this->auth->getAdmin(),
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

    public function showDelivery(string $uuid): View|RedirectResponse
    {
        try {
            $result = $this->notifications->getDelivery($uuid);
            $delivery = $result['data'] ?? [];

            return view('notifications.delivery', [
                'delivery'      => $delivery,
                'correlationId' => $result['correlation_id'] ?? null,
                'currentAdmin'  => $this->auth->getAdmin(),
            ]);
        } catch (ExternalServiceException $e) {
            return redirect()
                ->route('notifications.index')
                ->with('error', $e->getMessage())
                ->with('error_code', $e->errorCode);
        }
    }

    public function retryDelivery(string $uuid): RedirectResponse
    {
        try {
            $result = $this->notifications->retryDelivery($uuid);

            return redirect()
                ->route('notifications.delivery', $uuid)
                ->with('success', $result['message'] ?? 'Delivery retry accepted.')
                ->with('correlation_id', $result['correlation_id'] ?? null);
        } catch (ExternalServiceException $e) {
            return redirect()
                ->route('notifications.delivery', $uuid)
                ->with('error', $e->getMessage())
                ->with('error_code', $e->errorCode);
        }
    }
}
