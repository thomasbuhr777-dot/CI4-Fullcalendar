<?php

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class SidebarViewTest extends CIUnitTestCase
{
    public function testSidebarRendersWithoutCalendarContext(): void
    {
        $html = view('layout/sidebar');

        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringNotContainsString('id="calendarFilters"', $html);
    }

    public function testSidebarRendersCalendarToolsWithCalendarContext(): void
    {
        $html = view('layout/sidebar', [
            'calendars' => [[
                'id' => 7,
                'name' => 'Privat',
                'color' => '#2563EB',
            ]],
        ]);

        $this->assertStringContainsString('id="calendarFilters"', $html);
        $this->assertStringContainsString('Privat', $html);
    }
}
