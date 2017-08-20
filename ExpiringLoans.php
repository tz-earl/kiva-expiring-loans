<?php

/**
 * @file
 * A class for querying a Kiva GraphQL endpoint in order to fetch selective
 * data about loans that are expiring within a certain period of time.
 *
 */

declare(strict_types=1);

namespace Kiva;

class ExpiringLoans
{
    public function __construct()
    {
    }

    public function fetchExpiringLoans(int $hours_to_expiration)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://api.kivaws.org');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $result = curl_exec($ch);

        curl_close($ch);

        if ($result === false) {
            throw new Exception('Could not connect to Kiva, or timed out.');
        }

        return $result;
    }
}
