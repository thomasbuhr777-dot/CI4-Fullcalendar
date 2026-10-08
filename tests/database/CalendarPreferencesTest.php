<?php

use App\Models\CalendarPreferenceModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class CalendarPreferencesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace = ['App', 'CodeIgniter\Shield', 'CodeIgniter\Settings'];
    private User $user;
    private User $otherUser;
    private int $tenant;
    private int $otherTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $users = new UserModel();
        foreach (['settings-one', 'settings-two'] as $name) {
            $user = new User(['username' => $name, 'email' => $name . '@example.test', 'password' => 'Test-password-123!']);
            $users->save($user);
            $created[] = $users->findById($users->getInsertID());
        }
        [$this->user, $this->otherUser] = $created;
        foreach (['settings-tenant-one', 'settings-tenant-two'] as $slug) {
            $this->db->table('tenants')->insert(['name' => $slug, 'slug' => $slug, 'active' => 1]);
            $tenants[] = (int) $this->db->insertID();
        }
        [$this->tenant, $this->otherTenant] = $tenants;
        $this->db->table('tenant_users')->insert(['user_id' => $this->user->id, 'tenant_id' => $this->tenant, 'is_default' => 1]);
    }

    public function testPersistenceAcrossLoginAndIsolation(): void
    {
        $model = new CalendarPreferenceModel();
        $this->assertSame(CalendarPreferenceModel::defaults(), $model->preferences($this->user->id, $this->tenant));
        $this->actingAs($this->user);
        $saved = ['view' => 'timeGridWeek', 'state' => 'BY', 'years' => [2030]];
        $this->assertTrue($model->store($this->user->id, $this->tenant, $saved));
        auth()->logout();
        $this->actingAs($this->user);
        $this->assertSame($saved, (new CalendarPreferenceModel())->preferences(auth()->id(), $this->tenant));
        $this->assertSame(CalendarPreferenceModel::defaults(), $model->preferences($this->otherUser->id, $this->tenant));
        $this->assertSame(CalendarPreferenceModel::defaults(), $model->preferences($this->user->id, $this->otherTenant));
        $this->assertTrue($model->store($this->user->id, $this->otherTenant, ['view' => 'timeGridDay', 'state' => 'NI', 'years' => []]));
        $this->assertSame($saved, $model->preferences($this->user->id, $this->tenant));
    }

    public function testAuthenticatedPagesAndHolidayEndpoint(): void
    {
        $this->actingAs($this->user);
        \Config\Services::resetSingle('tenant');
        $settings = $this->get('settings');
        $settings->assertOK();
        $settings->assertSee('Niedersachsen');
        $settings->assertSee('Einstellungen');
        $settingsHtml = $settings->response()->getBody();
        (new \App\Models\CalendarModel())->createCalendar($this->tenant, 'Privat', '#2563EB');
        $calendar = $this->get('calendar');
        $calendar->assertOK();
        $this->assertStringContainsString('data-default-view="dayGridMonth"', $calendar->response()->getBody());
        $calendarHtml = $calendar->response()->getBody();
        (new CalendarPreferenceModel())->store($this->user->id, $this->tenant, ['view' => 'timeGridDay', 'state' => 'NI', 'years' => []]);
        $this->get('api/holidays?start=2030-01-01&end=2030-02-01')->assertJSONExact(['events' => [], 'messages' => []]);
        $this->get('api/holidays?start=wrong&end=wrong')->assertStatus(422);
        // Optional render export for local responsive QA, never calls the provider.
        if (getenv('CALENDAR_QA_EXPORT')) {
            file_put_contents(WRITEPATH . 'settings-qa.html', $settingsHtml);
            file_put_contents(WRITEPATH . 'calendar-qa.html', $calendarHtml);
        }
    }

    protected function tearDown(): void
    {
        \Config\Services::resetSingle('renderer');
        \Config\Services::resetSingle('tenant');
        parent::tearDown();
    }

    public function testExistingEventOperationsAndHolidayIdsRemainSeparated(): void
    {
        $this->actingAs($this->user);
        \Config\Services::resetSingle('tenant');
        $result = $this->post('api/calendars', ['name' => 'Privat', 'color' => '#2563EB']);
        $result->assertStatus(201);
        $calendarId = json_decode($result->response()->getBody(), true)['calendar']['id'];
        $data = ['calendar_id' => $calendarId, 'title' => 'Eigener Termin', 'start' => '2030-01-01T10:00', 'end' => '2030-01-01T11:00'];
        $result = $this->post('api/events', $data);
        $result->assertStatus(201);
        $id = json_decode($result->response()->getBody(), true)['id'];
        $this->get('api/events/' . $id)->assertOK();
        $this->post('api/events/' . $id, array_replace($data, ['title' => 'Geändert']))->assertJSONFragment(['success' => true]);
        $this->post('api/events/' . $id . '/move', ['start' => '2030-01-02T10:00', 'end' => '2030-01-02T12:00'])->assertJSONFragment(['success' => true]);
        $this->get('api/events?start=2030-01-01&end=2030-02-01&calendar_ids=')->assertJSONExact([]);
        $this->delete('api/events/' . $id)->assertJSONFragment(['success' => true]);
        $this->get('api/events/' . $id)->assertStatus(404);
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('api/events/holiday-NI-test');
    }

    public function testSettingsPostValidationAndImmediateDefaultView(): void
    {
        $this->actingAs($this->user);
        \Config\Services::resetSingle('tenant');
        $security = service('security');
        $data = ['view' => 'timeGridWeek', 'state' => 'NI', 'years' => ['2030']];
        $this->post('settings', $data + [$security->getTokenName() => $security->getHash()])->assertRedirectTo(site_url('calendar'));
        $result = $this->get('calendar');
        $this->assertStringContainsString('data-default-view="timeGridWeek"', $result->response()->getBody());
        $this->post('settings', array_replace($data, ['state' => 'ZZ']) + [$security->getTokenName() => $security->getHash()])->assertStatus(422);
        $this->assertSame('NI', (new CalendarPreferenceModel())->preferences($this->user->id, $this->tenant)['state']);
    }

    public function testProtectedEndpointsRequireLogin(): void
    {
        auth()->logout();
        \Config\Services::resetSingle('tenant');
        $this->get('settings')->assertRedirect();
        $this->get('api/holidays?start=2030-01-01&end=2030-02-01')->assertRedirect();
    }
}
