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

        // Check that loan count is all loans and total loan amt is correct.
        $inst = new ExpiringLoans('testdata');
        $expiring = $inst->fetchExpiringLoans();
        $this->assertCount(14, $expiring, 'Expected loan count not correct');

        $total = $inst->totalAmount();
        $this->assertEquals($total, 29300, 'Expected total loan amount not correct');
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

        // Check that loan count is all loans and total loan amt is zero.
        $inst = new ExpiringLoans('testdata');
        $expiring = $inst->fetchExpiringLoans();
        $this->assertCount(0, $expiring, 'Expected loan count not correct');

        $total = $inst->totalAmount();
        $this->assertEquals($total, 0, 'Expected total loan amount not correct');
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

        // Check that loan count is some loans and total loan amt is correct.
        $inst = new ExpiringLoans('testdata');
        $expiring = $inst->fetchExpiringLoans();
        $this->assertCount(5, $expiring, 'Expected loan count not correct');

        $total = $inst->totalAmount();
        $this->assertEquals($total, 10125, 'Expected total loan amount not correct');

        // Check for all the specific loan ids among those expiring soon.
        $id_list = [];
        foreach ($expiring as $loan) {
            $id_list[] = $loan->id;
        }
        $target_ids = [1349015, 1346391, 1344497, 1350312, 1350845];
        $this->assertEquals($id_list, $target_ids);
    }

    /**
     * Test case where some loans are expiring within 24 hours, but not all,
     * and their expiry times vary.
     */
    public function testVaryingExpiration()
    {
        $testdata = file_get_contents(TEST_BASE);

        // Set the expiry times for loans that are expiring later.
        // Start with 25 hours from now, and increment by 2 hours.
        for ($hours = 25; strpos($testdata, 'expires-later') !== false; $hours += 2) {
            $later_str = gmstrftime('%c', time() + $hours * 60 * 60);
            $testdata = preg_replace('/expires-later/', $later_str, $testdata, 1);
        }

        // Set the expiry times for loans that are expiring soon.
        // Start with right now, and increment by 3 hours.
        for ($hours = 0; strpos($testdata, 'expiring-soon') !== false; $hours += 3) {
            $soon_str = gmstrftime('%c', time() + $hours * 60 * 60);
            $testdata = preg_replace('/expiring-soon/', $soon_str, $testdata, 1);
        }

        $bytes_written = file_put_contents(TEST_FILENAME, $testdata);
        assert($bytes_written > 0);

        // Check that loan count is some of the loans and the total loan amt is correct.
        $inst = new ExpiringLoans('testdata');
        $expiring = $inst->fetchExpiringLoans();
        $this->assertCount(5, $expiring, 'Expected loan count not correct');

        $total = $inst->totalAmount();
        $this->assertEquals($total, 10125, 'Expected total loan amount not correct');

        // Check for all the specific loan ids among those expiring soon.
        $id_list = [];
        foreach ($expiring as $loan) {
            $id_list[] = $loan->id;
        }
        $target_ids = [1349015, 1346391, 1344497, 1350312, 1350845];
        $this->assertEquals($id_list, $target_ids);
    }
}
