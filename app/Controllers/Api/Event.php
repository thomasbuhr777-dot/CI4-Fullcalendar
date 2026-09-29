<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CalendarModel;
use App\Models\EventModel;
use CodeIgniter\HTTP\ResponseInterface;

class Event extends BaseController
{
    public function index(): ResponseInterface
    {
        $tenantId = service('tenant')->id();
        if ($tenantId === null) {
            return $this->response->setStatusCode(403)->setJSON([]);
        }

        $start = (string) ($this->request->getGet('start') ?? date('Y-m-01 00:00:00'));
        $end = (string) ($this->request->getGet('end') ?? date('Y-m-t 23:59:59'));
        $events = (new EventModel())->calendarEvents(
            $tenantId,
            $start,
            $end,
            $this->requestedCalendarIds($tenantId)
        );

        return $this->response->setJSON(array_map(static fn (array $event): array => [
            'id'              => (int) $event['id'],
            'calendar_id'     => (int) $event['calendar_id'],
            'title'           => $event['title'],
            'start'           => $event['start'],
            'end'             => $event['end'],
            'allDay'          => (bool) $event['all_day'],
            'backgroundColor' => $event['calendar_color'],
            'borderColor'     => $event['calendar_color'],
            'textColor'       => '#FFFFFF',
        ], $events));
    }

    public function create(): ResponseInterface
    {
        $tenantId = service('tenant')->id();
        if ($tenantId === null) {
            return $this->error('Kein aktiver Mandant.', 403);
        }

        $calendar = $this->selectedCalendar($tenantId, true);
        if ($calendar === null) {
            return $this->error('Der gewählte Kalender gehört nicht zum aktiven Mandanten.', 422);
        }

        $start = $this->dateTime((string) $this->request->getPost('start'));
        $title = trim((string) $this->request->getPost('title'));
        if ($start === null || $title === '') {
            return $this->error('Titel und Beginn sind erforderlich.', 422);
        }

        $id = (new EventModel())->insert([
            'tenant_id'   => $tenantId,
            'calendar_id' => (int) $calendar['id'],
            'category_id' => null,
            'title'       => $title,
            'description' => trim((string) $this->request->getPost('description')),
            'start'       => $start,
            'end'         => $this->dateTime((string) $this->request->getPost('end')),
            'all_day'     => $this->request->getPost('all_day') ? 1 : 0,
            'created_by'  => auth()->id(),
        ], true);

        if ($id === false) {
            return $this->error('Termin konnte nicht gespeichert werden.', 500);
        }

        return $this->response->setStatusCode(201)->setJSON(['success' => true, 'id' => (int) $id]);
    }

    public function show($id): ResponseInterface
    {
        $tenantId = service('tenant')->id();
        $event = $tenantId === null ? null : (new EventModel())->findTenantEvent($tenantId, (int) $id);
        if ($event === null) {
            return $this->error('Termin nicht gefunden.', 404);
        }

        $event['id'] = (int) $event['id'];
        $event['calendar_id'] = (int) $event['calendar_id'];

        return $this->response->setJSON($event);
    }

    public function update($id): ResponseInterface
    {
        $tenantId = service('tenant')->id();
        $model = new EventModel();
        if ($tenantId === null) {
            return $this->error('Kein aktiver Mandant.', 403);
        }
        if ($model->findTenantEvent($tenantId, (int) $id) === null) {
            return $this->error('Termin nicht gefunden.', 404);
        }

        $calendar = $this->selectedCalendar($tenantId, false);
        if ($calendar === null) {
            return $this->error('Der gewählte Kalender gehört nicht zum aktiven Mandanten.', 422);
        }

        $start = $this->dateTime((string) $this->request->getPost('start'));
        $title = trim((string) $this->request->getPost('title'));
        if ($start === null || $title === '') {
            return $this->error('Titel und Beginn sind erforderlich.', 422);
        }

        $ok = $model->updateTenantEvent($tenantId, (int) $id, [
            'calendar_id' => (int) $calendar['id'],
            'title'       => $title,
            'description' => trim((string) $this->request->getPost('description')),
            'start'       => $start,
            'end'         => $this->dateTime((string) $this->request->getPost('end')),
            'all_day'     => $this->request->getPost('all_day') ? 1 : 0,
        ]);

        return $this->response->setJSON(['success' => $ok]);
    }

    public function move($id): ResponseInterface
    {
        $tenantId = service('tenant')->id();
        $model = new EventModel();
        if ($tenantId === null) {
            return $this->error('Kein aktiver Mandant.', 403);
        }
        if ($model->findTenantEvent($tenantId, (int) $id) === null) {
            return $this->error('Termin nicht gefunden.', 404);
        }

        $start = $this->dateTime((string) $this->request->getPost('start'));
        if ($start === null) {
            return $this->error('Ein gültiger Beginn ist erforderlich.', 422);
        }

        $ok = $model->updateTenantEvent($tenantId, (int) $id, [
            'start' => $start,
            'end'   => $this->dateTime((string) $this->request->getPost('end')),
        ]);

        return $this->response->setJSON(['success' => $ok]);
    }

    public function delete($id): ResponseInterface
    {
        $tenantId = service('tenant')->id();
        if ($tenantId === null) {
            return $this->error('Kein aktiver Mandant.', 403);
        }

        $model = new EventModel();
        if ($model->findTenantEvent($tenantId, (int) $id) === null) {
            return $this->error('Termin nicht gefunden.', 404);
        }

        return $this->response->setJSON(['success' => $model->deleteTenantEvent($tenantId, (int) $id)]);
    }

    private function selectedCalendar(int $tenantId, bool $allowDefault): ?array
    {
        $model = new CalendarModel();
        $calendarId = filter_var($this->request->getPost('calendar_id'), FILTER_VALIDATE_INT);
        if ($calendarId === false || $calendarId === null) {
            return $allowDefault ? $model->defaultCalendar($tenantId) : null;
        }

        return $model->findTenantCalendar($tenantId, (int) $calendarId);
    }

    /** @return list<int>|null */
    private function requestedCalendarIds(int $tenantId): ?array
    {
        $raw = $this->request->getGet('calendar_ids');
        if ($raw === null) {
            return null;
        }
        if ($raw === '') {
            return [];
        }

        $requested = array_values(array_unique(array_filter(
            array_map('intval', explode(',', (string) $raw)),
            static fn (int $id): bool => $id > 0
        )));

        return (new CalendarModel())->ownedIds($tenantId, $requested);
    }

    private function dateTime(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        $date = \DateTimeImmutable::createFromFormat(strlen($value) === 16 ? 'Y-m-d H:i' : 'Y-m-d H:i:s', $value);

        return $date === false ? null : $date->format('Y-m-d H:i:s');
    }

    private function error(string $message, int $status): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success' => false,
            'message' => $message,
        ]);
    }
}
