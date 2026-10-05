<?php

namespace Tests\Feature\Admin;

use App\Support\AdminDate;
use Illuminate\Support\Carbon;

/**
 * Carbon's "tk" locale numbers weekdays from Monday, so its Turkmen weekday
 * names are one day off. The admin uses AdminDate for Turkmen labels.
 */
class AdminDateTest extends AdminTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function turkmen_weekday_and_month_names_match_the_real_date()
    {
        $monday = Carbon::parse('2026-10-05 14:30');

        $this->assertSame('Duşenbe, 5 Oktýabr', AdminDate::long($monday, 'tm'));
        $this->assertSame('Duş', AdminDate::weekdayShort($monday, 'tm'));
        $this->assertSame('Ýek', AdminDate::weekdayShort(Carbon::parse('2026-10-04'), 'tm'));
        $this->assertSame('5 Okt, 14:30', AdminDate::dayMonthTime($monday, 'tm'));
        $this->assertSame('Monday, 5 October', AdminDate::long($monday, 'en'));
    }

    /** @test */
    public function the_dashboard_shows_the_correct_turkmen_weekday()
    {
        Carbon::setTestNow('2026-10-05 10:00');

        $this->get($this->adminUrl('dashboard'))
            ->assertOk()
            ->assertSee('Duşenbe, 5 Oktýabr')
            ->assertDontSee('Sişenbe, 5');
    }
}
