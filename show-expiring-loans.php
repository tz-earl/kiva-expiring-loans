<?php

/**
 * @file
 * Render Kiva expiring loans.
 *
 */

include 'ExpiringLoans.php';

use Kiva\ExpiringLoans;

function showExpiringLoans()
{
    $exp_loans = new ExpiringLoans;
    $expiring = $exp_loans->fetchExpiringLoans();

    // Debugging.
    print count($expiring);
    print '<br />';
    print_r($expiring);
}

showExpiringLoans();
