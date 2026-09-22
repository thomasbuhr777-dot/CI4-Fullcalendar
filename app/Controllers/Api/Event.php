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

    // Hauptkalender des Mandanten holen
    $calendar = db_connect()
        ->table('calendars')
        ->where('tenant_id', $tenantId)
        ->orderBy('id')
        ->get()
        ->getRowArray();

    if (! $calendar) {
        return $this->response
            ->setStatusCode(500)
            ->setJSON([
                'success' => false,
                'message' => 'Kein Kalender gefunden.',
            ]);
    }

    // HTML datetime-local → MySQL DATETIME
    $start = $this->request->getPost('start');
    $end   = $this->request->getPost('end');

    $start = $start ? str_replace('T', ' ', $start) . ':00' : null;
    $end   = $end ? str_replace('T', ' ', $end) . ':00' : null;

    // Checkbox liefert nur einen Wert, wenn sie angehakt ist
    $allDay = $this->request->getPost('all_day') ? 1 : 0;

    $model = new \App\Models\EventModel();

    $id = $model->insert([
        'tenant_id'   => $tenantId,
        'calendar_id' => $calendar['id'],
        'category_id' => null,

        'title'       => $this->request->getPost('title'),
        'description' => $this->request->getPost('description'),

        'start'       => $start,
        'end'         => $end,

        'all_day'     => $allDay,

        'created_by'  => auth()->id(),
    ]);

    return $this->response->setJSON([
        'success' => true,
        'id'      => $id,
    ]);
}

public function show($id)
{
    $event = (new EventModel())
        ->findTenantEvent(service('tenant')->id(), (int)$id);

    return $this->response->setJSON($event ?? []);
}

public function update($id)
{
    $model = new EventModel();

    $ok = $model->updateTenantEvent(
        service('tenant')->id(),
        (int)$id,
        [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'start'   => $this->request->getPost('start'), 
            'end'=> $this->request->getPost('end'),
            'all_day' => (bool) $this->request->getPost('all_day'),
        ]
    );

    return $this->response->setJSON([
        'success' => $ok
    ]);
}

public function delete($id)
{
    $tenantId = service('tenant')->id();

    if ($tenantId === null) {
        return $this->response
            ->setStatusCode(403)
            ->setJSON(['success' => false]);
    }

    $model = new EventModel();

    // Erst prüfen, ob der Termin zum Mandanten gehört
    $event = $model->findTenantEvent($tenantId, (int) $id);

    if (! $event) {
        return $this->response
            ->setStatusCode(404)
            ->setJSON(['success' => false]);
    }

    $model->delete($id);

    return $this->response->setJSON([
        'success' => true,
    ]);
}

}