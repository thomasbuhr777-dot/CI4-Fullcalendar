<?php

namespace App\Models;

use CodeIgniter\Model;
use InvalidArgumentException;

class CalendarPreferenceModel extends Model
{
    protected $table = 'calendar_preferences';
    protected $allowedFields = ['user_id', 'tenant_id', 'view', 'state', 'years'];

    public const STATES = [
        'BW' => 'Baden-Württemberg', 'BY' => 'Bayern', 'BE' => 'Berlin', 'BB' => 'Brandenburg',
        'HB' => 'Bremen', 'HH' => 'Hamburg', 'HE' => 'Hessen', 'MV' => 'Mecklenburg-Vorpommern',
        'NI' => 'Niedersachsen', 'NW' => 'Nordrhein-Westfalen', 'RP' => 'Rheinland-Pfalz',
        'SL' => 'Saarland', 'SN' => 'Sachsen', 'ST' => 'Sachsen-Anhalt',
        'SH' => 'Schleswig-Holstein', 'TH' => 'Thüringen',
    ];
    public const VIEWS = ['dayGridMonth' => 'Monat', 'timeGridWeek' => 'Woche', 'timeGridDay' => 'Tag'];

    public static function defaults(): array
    {
        $year = (int) date('Y');
        return ['view' => 'dayGridMonth', 'state' => 'NI', 'years' => [$year, $year + 1]];
    }

    public function preferences(int $user, int $tenant): array
    {
        $row = $this->where(['user_id' => $user, 'tenant_id' => $tenant])->first();
        return $row ? ['view' => $row['view'], 'state' => $row['state'], 'years' => json_decode($row['years'], true)] : self::defaults();
    }

    public static function validated(array $data): array
    {
        if (!is_string($data['view'] ?? null) || !isset(self::VIEWS[$data['view']])
            || !is_string($data['state'] ?? null) || !isset(self::STATES[$data['state']])
            || !is_array($data['years'] ?? null) || count($data['years']) > 20) {
            throw new InvalidArgumentException('Bitte gültige Ansicht, Bundesland und höchstens 20 Jahre wählen.');
        }
        $years = [];
        foreach ($data['years'] as $year) {
            if ((!is_int($year) && !is_string($year)) || !preg_match('/^\d{4}$/D', (string) $year) || (int) $year < 2000 || (int) $year > 2100) {
                throw new InvalidArgumentException('Feiertagsjahre müssen zwischen 2000 und 2100 liegen.');
            }
            $years[] = (int) $year;
        }
        $years = array_values(array_unique($years));
        sort($years);
        return ['view' => $data['view'], 'state' => $data['state'], 'years' => $years];
    }

    public function store(int $user, int $tenant, array $data): bool
    {
        $data = self::validated($data);
        $data['years'] = json_encode($data['years']);
        $row = $this->where(['user_id' => $user, 'tenant_id' => $tenant])->first();
        return $row ? $this->update($row['id'], $data) : $this->insert($data + ['user_id' => $user, 'tenant_id' => $tenant]) !== false;
    }
}
