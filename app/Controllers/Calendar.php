<?php

namespace App\Controllers;

use App\Models\CalendarModel;

class Calendar extends BaseController
{
    public function index()
    {
        // Aktiver Tenant aus der Session
        $tenantId = (int) session('tenant_id');

        $calendarModel = new CalendarModel();

        $calendars = [];

        // Nur laden, wenn ein Tenant vorhanden ist
        if ($tenantId > 0) {
            $calendars = $calendarModel->forTenant($tenantId);
        }

        return view('calendar/index', [
            'title'     => 'Kalender',
            'calendars' => $calendars,
        ]);
    }
}