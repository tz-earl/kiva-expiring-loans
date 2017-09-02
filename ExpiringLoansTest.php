<?php
declare(strict_types=1);

namespace Kiva;

use PHPUnit\Framework\TestCase;

final class ExpiringLoansTest extends TestCase
{
    public function testMakeTestInstance()
    {
        $this->assertInstanceOf(
            ExpiringLoans::class,
            new ExpiringLoans('testdata')
        );
    }

    /**
     * Test case where all loans are expiring within 24 hours.
     */
    public function testAllExpiring()
    {
        // Make the expiry time "now" for all loans in the test input.
        $testdata = file_get_contents(TEST_BASE);
        $now_str = gmstrftime('%c', time());
        $testdata = preg_replace(['/expires-later/', '/expiring-soon/'], $now_str, $testdata);
        $bytes_written = file_put_contents(TEST_FILENAME, $testdata);
        assert($bytes_written > 0);

        // Check that loan count is all loans in test file.
        $inst = new ExpiringLoans('testdata');
        $expiring = $inst->fetchExpiringLoans();
        $this->assertCount(14, $expiring, 'Expected loan count not correct');
    }

    /**
     * Test case where no loans are expiring within 24 hours.
     */
    public function testNoneExpiring()
    {
        // Make the expiry time "now + 24.5 hours" for all loans in the test input.
        $testdata = file_get_contents(TEST_BASE);
        $later_str = gmstrftime('%c', intval(time() + 24.5 * 60 * 60));
        $testdata = preg_replace(['/expires-later/', '/expiring-soon/'], $later_str, $testdata);
        $bytes_written = file_put_contents(TEST_FILENAME, $testdata);
        assert($bytes_written > 0);

        // Check that loan count is all loans in test file.
        $inst = new ExpiringLoans('testdata');
        $expiring = $inst->fetchExpiringLoans();
        $this->assertCount(0, $expiring, 'Expected loan count not correct');
    }

    /**
     * Test case where some loans are expiring within 24 hours, but not all.
     */
    public function testSomeExpiring()
    {
        // Make the expiry time "now + one hour" for 'expiring-soon' loans in the test input.
        // Make the expiry time "now + 25 hours" for 'expires-later' loans in the test input.
        $testdata = file_get_contents(TEST_BASE);
        $soon_str = gmstrftime('%c', time() + 1 * 60 * 60);
        $testdata = preg_replace('/expiring-soon/', $soon_str, $testdata);
        $later_str = gmstrftime('%c', time() + 25 * 60 * 60);
        $testdata = preg_replace('/expires-later/', $later_str, $testdata);
        $bytes_written = file_put_contents(TEST_FILENAME, $testdata);
        assert($bytes_written > 0);

        // Check that loan count is some loans in test file.
        $inst = new ExpiringLoans('testdata');
        $expiring = $inst->fetchExpiringLoans();
        $this->assertCount(5, $expiring, 'Expected loan count not correct');
    }
}
