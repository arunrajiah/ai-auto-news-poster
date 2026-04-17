<?php
/**
 * Tests for AANP_Scheduler — cron schedule registration and state helpers.
 */

use PHPUnit\Framework\TestCase;

class SchedulerTest extends TestCase {

    /**
     * register_cron_schedules() should add the 'aanp_every6hours' entry with
     * the correct interval (21600 seconds = 6 hours).
     */
    public function test_register_cron_schedules_adds_every6hours(): void {
        $result = ( new AANP_Scheduler() )->register_cron_schedules( array() );

        $this->assertArrayHasKey( 'aanp_every6hours', $result );
        $this->assertSame( 21600, $result['aanp_every6hours']['interval'] );
    }

    /**
     * get_current_schedule() should return 'disabled' when wp_get_scheduled_event()
     * returns false (nothing scheduled — as stubbed in tests/stubs.php).
     */
    public function test_get_current_schedule_returns_disabled_when_nothing_scheduled(): void {
        $this->assertSame( 'disabled', ( new AANP_Scheduler() )->get_current_schedule() );
    }

    /**
     * get_next_run() should return null when wp_next_scheduled() returns false
     * (nothing scheduled — as stubbed in tests/stubs.php).
     */
    public function test_get_next_run_returns_null_when_nothing_scheduled(): void {
        $this->assertNull( ( new AANP_Scheduler() )->get_next_run() );
    }

    /**
     * ALLOWED_SCHEDULES should contain the four core schedule keys plus 'disabled'.
     */
    public function test_allowed_schedules_constant(): void {
        $allowed = AANP_Scheduler::ALLOWED_SCHEDULES;

        $this->assertContains( 'disabled', $allowed );
        $this->assertContains( 'hourly', $allowed );
        $this->assertContains( 'daily', $allowed );
        $this->assertContains( 'aanp_every6hours', $allowed );
    }
}
