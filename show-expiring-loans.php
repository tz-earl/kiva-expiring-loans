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
    $result = $exp_loans->fetchExpiringLoans(24);

    // Debugging.
    print count($result);
    print '<br />';
    print_r($result);
}

showExpiringLoans();
