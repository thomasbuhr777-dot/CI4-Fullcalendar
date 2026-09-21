<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EventModel;

class Event extends BaseController
{
   public function index()
{
    $start = $this->request->getGet('start');
    $end   = $this->request->getGet('end');

    // Direkter Browseraufruf ohne Parameter
    if ($start === null || $end === null) {
        $start = date('Y-m-01 00:00:00');
        $end   = date('Y-m-t 23:59:59');
    }

    $tenantId = service('tenant')->id();

    if ($tenantId === null) {
        return $this->response->setJSON([]);
    }

    $events = (new EventModel())->calendarEvents($tenantId, $start, $end);

    $result = [];

    foreach ($events as $event) {
        $result[] = [
            'id'              => $event['id'],
            'title'           => $event['title'],
            'start'           => $event['start'],
            'end'             => $event['end'],
            'allDay'          => (bool) $event['all_day'],
            'backgroundColor' => $event['color'],
            'borderColor'     => $event['color'],
        ];
    }

    return $this->response->setJSON($result);
}
}