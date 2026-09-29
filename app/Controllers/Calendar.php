<?php

namespace App\Controllers;

use App\Models\CalendarModel;

class Calendar extends BaseController
{
    public function index()
    {
        $tenantId = service('tenant')->id();

        $calendarModel = new CalendarModel();

        $calendars = [];

        // Nur laden, wenn ein Tenant vorhanden ist
        if ($tenantId !== null) {
            $calendars = $calendarModel->forTenant($tenantId);
        }

        return view('calendar/index', [
            'title'     => 'Kalender',
            'calendars' => $calendars,
            'tenantId'  => $tenantId,
        ]);
    }
}
