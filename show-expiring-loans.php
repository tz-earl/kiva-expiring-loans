<?php

/**
 * @file
 *
 */

include 'ExpiringLoans.php';

use Kiva\ExpiringLoans;

function showExpiringLoans()
{
    $exp_loans = new ExpiringLoans;
    print "result: " . $exp_loans->fetchExpiringLoans(24);
}

showExpiringLoans();
