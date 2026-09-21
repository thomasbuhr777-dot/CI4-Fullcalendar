<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table      = 'events';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

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

    protected $useTimestamps = true;

    /**
     * Liefert alle Termine eines Mandanten im gewünschten Zeitraum.
     */
    public function calendarEvents(int $tenantId, string $start, string $end): array
    {
        return $this->select('events.*, calendars.color')
            ->join('calendars', 'calendars.id = events.calendar_id')
            ->where('events.tenant_id', $tenantId)
            ->where('events.start >=', $start)
            ->where('events.start <=', $end)
            ->orderBy('events.start', 'ASC')
            ->findAll();
    }
}