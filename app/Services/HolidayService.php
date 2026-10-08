<?php

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

class HolidayService
{
    public function __construct(private object $client, private CacheInterface $cache, private string $lockPath)
    {
    }

    public function feed(array $preferences, string $start, string $end): array
    {
        $from = new DateTimeImmutable($start);
        $until = new DateTimeImmutable($end);
        if ($until <= $from || $until->diff($from)->days > 370) {
            throw new \InvalidArgumentException('Ungültiger Kalenderzeitraum.');
        }
        $events = $messages = [];
        foreach ($preferences['years'] as $year) {
            if ($year < (int) $from->format('Y') || $year > (int) $until->modify('-1 second')->format('Y')) {
                continue;
            }
            [$items, $message] = $this->year($preferences['state'], $year);
            if ($message) $messages[] = $message;
            foreach ($items as $item) {
                if ($item['start'] >= $from->format('Y-m-d') && $item['start'] < $until->format('Y-m-d')) {
                    $item['extendedProps']['stale'] = $message !== null;
                    $events[] = $item;
                }
            }
        }
        return ['events' => $events, 'messages' => array_values(array_unique($messages))];
    }

    private function year(string $state, int $year): array
    {
        $key = "holidays_{$state}_{$year}";
        $cached = $this->cache->get($key);
        if ($cached && $cached['fetched'] > time() - 86400) return [$cached['events'], null];
        $fallback = static fn () => [$cached['events'] ?? [], $cached
            ? "Feiertage {$year}: zwischengespeicherte Daten sind veraltet; Aktualisierung derzeit nicht möglich."
            : "Feiertage {$year} sind derzeit nicht verfügbar. Eigene Termine bleiben nutzbar."];
        $lock = fopen($this->lockPath, 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock) fclose($lock);
            return $fallback();
        }
        try {
            // Serialize all outgoing calls on this installation, including the rolling budget.
            $cached = $this->cache->get($key);
            if ($cached && $cached['fetched'] > time() - 86400) return [$cached['events'], null];
            if ($this->cache->get($key . '_retry')) return $fallback();
            $calls = array_values(array_filter($this->cache->get('holiday_calls') ?? [], static fn ($time) => $time > time() - 3600));
            if (count($calls) >= 90) return $fallback();
            $calls[] = time();
            if (!$this->cache->save('holiday_calls', $calls, 3600)) return $fallback();
            $this->cache->save($key . '_retry', true, 900);
            $response = $this->client->get('https://get.api-feiertage.de/', [
                'query' => ['years' => (string) $year, 'states' => strtolower($state)],
                'timeout' => 5, 'connect_timeout' => 2, 'http_errors' => false,
            ]);
            if ($response->getStatusCode() !== 200) throw new RuntimeException('HTTP error');
            $body = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($body) || ($body['status'] ?? null) !== 'success' || !is_array($body['feiertage'] ?? null) || !array_is_list($body['feiertage'])) {
                throw new RuntimeException('Invalid holiday response');
            }
            $events = self::normalize($body['feiertage'], $state, $year);
            if ($events === []) return $fallback();
            $this->cache->save($key, ['fetched' => time(), 'events' => $events], 86400 * 90);
            $this->cache->delete($key . '_retry');
            return [$events, null];
        } catch (Throwable $exception) {
            log_message('warning', 'Holiday provider unavailable: {message}', ['message' => $exception->getMessage()]);
            return $fallback();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public static function normalize(array $rows, string $state, int $year): array
    {
        $events = [];
        foreach ($rows as $row) {
            if (!is_array($row) || !is_string($row['date'] ?? null) || !is_string($row['fname'] ?? null)) {
                throw new RuntimeException('Invalid holiday entry');
            }
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $row['date']);
            if (!$date || $date->format('Y-m-d') !== $row['date'] || trim($row['fname']) === '') {
                throw new RuntimeException('Invalid holiday date or name');
            }
            if ((int) $date->format('Y') !== $year || (!in_array($row['all_states'] ?? 0, [1, '1', true], true)
                && !in_array($row[strtolower($state)] ?? 0, [1, '1', true], true))) continue;
            $id = 'holiday-' . $state . '-' . $row['date'] . '-' . sha1($row['fname']);
            $events[$id] = [
                'id' => $id, 'title' => $row['fname'], 'start' => $row['date'],
                'allDay' => true, 'editable' => false, 'startEditable' => false,
                'durationEditable' => false, 'classNames' => ['holiday-event'],
                'backgroundColor' => '#fff3cd', 'borderColor' => '#997404', 'textColor' => '#664d03',
                'extendedProps' => ['holiday' => true],
            ];
        }
        return array_values($events);
    }
}
