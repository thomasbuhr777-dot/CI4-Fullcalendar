<?php

use App\Models\CalendarModel;
use App\Models\EventModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** @internal */
final class TenantCalendarIsolationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    private int $tenantOne;
    private int $tenantTwo;
    private int $calendarOne;
    private int $calendarTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $now = '2026-09-29 10:00:00';
        $this->db->table('tenants')->insert([
            'name' => 'Mandant Eins', 'slug' => 'tenant-one', 'active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->tenantOne = (int) $this->db->insertID();

        $this->db->table('tenants')->insert([
            'name' => 'Mandant Zwei', 'slug' => 'tenant-two', 'active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->tenantTwo = (int) $this->db->insertID();

        $this->db->table('calendars')->insert([
            'tenant_id' => $this->tenantOne, 'name' => 'Blau', 'color' => '#2563EB',
            'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->calendarOne = (int) $this->db->insertID();

        $this->db->table('calendars')->insert([
            'tenant_id' => $this->tenantTwo, 'name' => 'Rot', 'color' => '#DC2626',
            'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->calendarTwo = (int) $this->db->insertID();
    }

    public function testCalendarLookupRejectsAnotherTenantCalendar(): void
    {
        $model = new CalendarModel();

        $this->assertNotNull($model->findTenantCalendar($this->tenantOne, $this->calendarOne));
        $this->assertNull($model->findTenantCalendar($this->tenantOne, $this->calendarTwo));
        $this->assertSame([$this->calendarOne], $model->ownedIds(
            $this->tenantOne,
            [$this->calendarOne, $this->calendarTwo]
        ));
    }

    public function testEventFeedKeepsTenantAndCalendarAssignmentTogether(): void
    {
        $this->db->disableForeignKeyChecks();
        $this->insertEvent($this->tenantOne, $this->calendarOne, 'Eigener Termin');
        $this->insertEvent($this->tenantTwo, $this->calendarTwo, 'Fremder Termin');
        $this->db->enableForeignKeyChecks();

        $events = (new EventModel())->calendarEvents(
            $this->tenantOne,
            '2026-09-01 00:00:00',
            '2026-10-01 00:00:00',
            [$this->calendarOne]
        );

        $this->assertCount(1, $events);
        $this->assertSame('Eigener Termin', $events[0]['title']);
        $this->assertSame($this->calendarOne, (int) $events[0]['calendar_id']);
        $this->assertSame('#2563EB', $events[0]['calendar_color']);
    }

    private function insertEvent(int $tenantId, int $calendarId, string $title): void
    {
        $this->db->table('events')->insert([
            'tenant_id' => $tenantId,
            'calendar_id' => $calendarId,
            'category_id' => null,
            'title' => $title,
            'description' => null,
            'start' => '2026-09-29 10:00:00',
            'end' => '2026-09-29 11:00:00',
            'all_day' => 0,
            'created_by' => 1,
            'created_at' => '2026-09-29 09:00:00',
            'updated_at' => '2026-09-29 09:00:00',
        ]);
    }
}
