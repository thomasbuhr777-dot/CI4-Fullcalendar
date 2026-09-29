<?php

namespace App\Models;

use CodeIgniter\Model;

class CalendarModel extends Model
{
    protected $table = 'calendars';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps = true;

    protected $allowedFields = ['tenant_id', 'name', 'color', 'is_active'];

    public function forTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function defaultCalendar(int $tenantId): ?array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function findTenantCalendar(int $tenantId, int $calendarId): ?array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->find($calendarId);
    }

    /** @param list<int> $calendarIds @return list<int> */
    public function ownedIds(int $tenantId, array $calendarIds): array
    {
        if ($calendarIds === []) {
            return [];
        }

        $rows = $this->select('id')
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->whereIn('id', $calendarIds)
            ->findAll();

        return array_map(static fn (array $row): int => (int) $row['id'], $rows);
    }

    public function createCalendar(int $tenantId, string $name, string $color): int|false
    {
        $id = $this->insert([
            'tenant_id' => $tenantId,
            'name'      => trim($name),
            'color'     => strtoupper($color),
            'is_active' => 1,
        ], true);

        return $id === false ? false : (int) $id;
    }
}
