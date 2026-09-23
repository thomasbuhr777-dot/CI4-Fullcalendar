<?php

namespace App\Models;

use CodeIgniter\Model;

class CalendarModel extends Model
{
    protected $table            = 'calendars';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'tenant_id',
        'name',
        'color',
    ];

    /**
     * Alle Kalender eines Tenants.
     */
    public function forTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Ersten Kalender des Tenants liefern.
     * (Vorbereitung für späteren Default-Kalender.)
     */
    public function defaultCalendar(int $tenantId): ?array
    {
        return $this->where('tenant_id', $tenantId)
                    ->orderBy('id', 'ASC')
                    ->first();
    }

    public function createCalendar(int $tenantId, string $name, string $color): bool
{
    return $this->insert([
        'tenant_id' => $tenantId,
        'name'      => $name,
        'color'     => $color,
    ]);
}
}