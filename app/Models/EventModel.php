<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table      = 'events';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'tenant_id',
        'calendar_id',
        'category_id',
        'title',
        'description',
        'start',
        'end',
        'all_day',
        'created_by',
    ];

    /**
     * Termine für FullCalendar inklusive Kalenderfarbe.
     */
    public function calendarEvents(int $tenantId, string $start, string $end): array
    {
        $events = $this->select([
                'events.*',
                'calendars.name AS calendar_name',
                'calendars.color',
            ])
            ->join('calendars', 'calendars.id = events.calendar_id')
            ->where('events.tenant_id', $tenantId)
            ->where('events.start >=', $start)
            ->where('events.start <=', $end)
            ->orderBy('events.start', 'ASC')
            ->findAll();

        foreach ($events as &$event) {

            $event['backgroundColor'] = $event['color'];
            $event['borderColor']     = $event['color'];
            $event['textColor']       = '#FFFFFF';

        }

        return $events;
    }

    /**
     * Einzelnen Termin eines Tenants finden.
     */
    public function findTenantEvent(int $tenantId, int $id): ?array
    {
        return $this->where('tenant_id', $tenantId)
                    ->find($id);
    }

    /**
     * Termin aktualisieren.
     */
    public function updateTenantEvent(int $tenantId, int $id, array $data): bool
    {
        return $this->where('tenant_id', $tenantId)
                    ->set($data)
                    ->update($id);
    }

    /**
     * Termin löschen.
     */
    public function deleteTenantEvent(int $tenantId, int $id): bool
    {
        return $this->where('tenant_id', $tenantId)
                    ->delete($id);
    }
}