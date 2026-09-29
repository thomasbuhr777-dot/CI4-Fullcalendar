<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'tenant_id', 'calendar_id', 'category_id', 'title', 'description',
        'start', 'end', 'all_day', 'created_by',
    ];

    /** @param list<int>|null $calendarIds null means all tenant calendars, [] means none */
    public function calendarEvents(int $tenantId, string $start, string $end, ?array $calendarIds = null): array
    {
        if ($calendarIds === []) {
            return [];
        }

        $builder = $this->select([
                'events.*',
                'calendars.name AS calendar_name',
                'calendars.color AS calendar_color',
            ])
            ->join('calendars', 'calendars.id = events.calendar_id AND calendars.tenant_id = events.tenant_id')
            ->where('events.tenant_id', $tenantId)
            ->where('calendars.is_active', 1)
            ->where('events.start <', $end)
            ->groupStart()
                ->groupStart()
                    ->where($this->db->protectIdentifiers('events.end') . ' IS NULL', null, false)
                    ->where('events.start >=', $start)
                ->groupEnd()
                ->orWhere('events.end >', $start)
            ->groupEnd()
            ->orderBy('events.start', 'ASC');

        if ($calendarIds !== null) {
            $builder->whereIn('events.calendar_id', $calendarIds);
        }

        return $builder->findAll();
    }

    public function findTenantEvent(int $tenantId, int $id): ?array
    {
        return $this->where('tenant_id', $tenantId)->find($id);
    }

    public function updateTenantEvent(int $tenantId, int $id, array $data): bool
    {
        if ($this->findTenantEvent($tenantId, $id) === null) {
            return false;
        }

        return $this->update($id, $data);
    }

    public function deleteTenantEvent(int $tenantId, int $id): bool
    {
        if ($this->findTenantEvent($tenantId, $id) === null) {
            return false;
        }

        return $this->delete($id);
    }
}
