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

public function create()
{
    $tenantId = service('tenant')->id();

    if ($tenantId === null) {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['success' => false]);
    }

    $calendarId = db_connect()
        ->table('calendars')
        ->where('tenant_id', $tenantId)
        ->orderBy('id')
        ->get()
        ->getRowArray()['id'];

    $userId = auth()->id();

    $model = new EventModel();

    $id = $model->insert([
        'tenant_id'   => $tenantId,
        'calendar_id' => $calendarId,
        'category_id' => null,

        'title'       => $this->request->getPost('title'),
        'description' => $this->request->getPost('description'),

        'start'       => $this->request->getPost('start'),
        'end'         => $this->request->getPost('start'),

        'all_day'     => 1,

        'created_by'  => $userId,
    ]);

    return $this->response->setJSON([
        'success' => true,
        'id'      => $id,
    ]);
}
}