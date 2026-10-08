<?php

use App\Models\CalendarPreferenceModel;
use App\Services\HolidayService;
use CodeIgniter\Cache\Handlers\FileHandler;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;

final class HolidayServiceTest extends CIUnitTestCase
{
    private FileHandler $cache;
    private object $client;
    private HolidayService $service;
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = WRITEPATH . 'cache/holiday-test-' . uniqid() . '/';
        mkdir($this->directory);
        $config = new \Config\Cache();
        $config->file['storePath'] = $this->directory;
        $this->cache = new FileHandler($config);
        $this->client = new class {
            public int $calls = 0;
            public array $queries = [];
            public int $status = 200;
            public bool $fail = false;
            public string $body = '';
            public function get($url, $options) {
                $this->calls++;
                $this->queries[] = $options['query'];
                if ($this->fail) throw new RuntimeException('Fake timeout');
                return (new Response(new \Config\App()))->setStatusCode($this->status)->setBody($this->body);
            }
        };
        $this->client->body = json_encode(['status' => 'success', 'feiertage' => [
            ['date' => '2030-01-01', 'fname' => 'Neujahr', 'all_states' => '1'],
            ['date' => '2030-10-31', 'fname' => 'Reformationstag', 'ni' => '1'],
            ['date' => '2030-11-01', 'fname' => 'Allerheiligen', 'ni' => '0', 'by' => '1'],
            ['date' => '2029-12-31', 'fname' => 'Vorjahr', 'all_states' => '1'],
        ]]);
        $this->service = new HolidayService($this->client, $this->cache, $this->directory . 'lock');
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '*') as $file) unlink($file);
        rmdir($this->directory);
        parent::tearDown();
    }

    private function feed(array $years = [2030], string $start = '2030-01-01', string $end = '2031-01-01'): array
    {
        return $this->service->feed(['state' => 'NI', 'years' => $years], $start, $end);
    }

    public function testDynamicDefaults(): void
    {
        $this->assertSame(['view' => 'dayGridMonth', 'state' => 'NI', 'years' => [(int) date('Y'), (int) date('Y') + 1]], CalendarPreferenceModel::defaults());
    }

    public function testInvalidPreferencesAreRejected(): void
    {
        foreach ([['view' => 'listWeek'], ['state' => 'XX'], ['years' => ['2030oops']], ['years' => [1999]], ['years' => [2101]], ['years' => '2030'], ['years' => [[2030]]]] as $invalid) {
            try {
                CalendarPreferenceModel::validated(array_replace(CalendarPreferenceModel::defaults(), $invalid));
                $this->fail('Invalid settings accepted');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame([], CalendarPreferenceModel::validated(['view' => 'timeGridDay', 'state' => 'BY', 'years' => []])['years']);
    }

    public function testApplicableReadOnlyHolidaysAndSharedCache(): void
    {
        $result = $this->feed();
        $this->assertSame(['Neujahr', 'Reformationstag'], array_column($result['events'], 'title'));
        foreach ($result['events'] as $event) {
            $this->assertTrue($event['allDay']);
            $this->assertFalse($event['editable']);
            $this->assertFalse($event['startEditable']);
            $this->assertFalse($event['durationEditable']);
            $this->assertTrue($event['extendedProps']['holiday']);
        }
        $otherUser = new HolidayService($this->client, $this->cache, $this->directory . 'lock');
        $this->assertSame($result, $otherUser->feed(['state' => 'NI', 'years' => [2030]], '2030-01-01', '2031-01-01'));
        $this->assertSame(1, $this->client->calls);
        $this->assertSame([['years' => '2030', 'states' => 'ni']], $this->client->queries);
    }

    public function testYearBoundaryAndSelectedYears(): void
    {
        $this->client->body = json_encode(['status' => 'success', 'feiertage' => [
            ['date' => '2029-12-31', 'fname' => 'Jahresende', 'all_states' => '1'],
            ['date' => '2030-01-01', 'fname' => 'Neujahr', 'all_states' => '1'],
        ]]);
        $result = $this->feed([2029, 2030, 2032], '2029-12-29T00:00:00+01:00', '2030-01-05T00:00:00+01:00');
        $this->assertCount(2, $result['events']);
        $this->assertSame(2, $this->client->calls);
        $this->assertSame(['Neujahr'], array_column($this->feed([2030], '2029-12-29', '2030-01-05')['events'], 'title'));
        $this->assertSame([], $this->feed([])['events']);
    }

    public function testMissingYearIsOnlyTemporarilySuppressed(): void
    {
        $this->client->body = '{"status":"success","feiertage":[]}';
        $this->assertNotEmpty($this->feed()['messages']);
        $this->assertNull($this->cache->get('holidays_NI_2030'));
        $this->feed();
        $this->assertSame(1, $this->client->calls);
        $this->cache->delete('holidays_NI_2030_retry');
        $this->feed();
        $this->assertSame(2, $this->client->calls);
    }

    public function testTimeoutServesStaleCache(): void
    {
        $events = $this->feed()['events'];
        $this->cache->save('holidays_NI_2030', ['fetched' => time() - 90000, 'events' => $events], 3600);
        $this->client->fail = true;
        $result = $this->feed();
        $this->assertCount(2, $result['events']);
        $this->assertTrue($result['events'][0]['extendedProps']['stale']);
        $this->assertStringContainsString('veraltet', $result['messages'][0]);
    }

    public function testProviderFailuresNeverBecomeSuccessfulEmptyCache(): void
    {
        foreach ([[500, '{}'], [200, 'invalid'], [200, '{"status":"error","feiertage":[]}'], [200, '{"status":"success","feiertage":{}}'], [200, '{"status":"success","feiertage":[{"date":"2030-02-30","fname":"Wrong"}]}']] as [$status, $body]) {
            $this->cache->delete('holidays_NI_2030_retry');
            $this->client->status = $status;
            $this->client->body = $body;
            $this->assertNotEmpty($this->feed()['messages']);
            $this->assertNull($this->cache->get('holidays_NI_2030'));
        }
    }

    public function testRollingBudgetStopsOutgoingCalls(): void
    {
        $this->cache->save('holiday_calls', array_fill(0, 90, time()), 3600);
        $this->assertNotEmpty($this->feed()['messages']);
        $this->assertSame(0, $this->client->calls);
    }
}
