<?php

/**
 * @file
 * A class for querying a Kiva GraphQL endpoint in order to fetch selective
 * data about loans that are expiring within a certain period of time.
 *
 */

declare(strict_types=1);

namespace Kiva;

use \Exception;

class ExpiringLoans
{
    public function __construct()
    {
    }

    public function fetchExpiringLoans()
    {
        $api_url = 'http://api.kivaws.org/graphql';

        $limit = 1000;  // No apparent problem handling this "batch" size for the query,
                        // but have had problems with 3500 producing an empty result set.

        $cumulative = [];

        // Fetch all loans that are fundRaising.
        for ($offset = 0;; $offset += $limit) {
            $query = urlencode('{ loans (filters: { status: fundRaising },' .
                " offset:$offset, limit: $limit, sortBy: newest)" .
                ' { totalCount values { id loanAmount fundedAmount plannedExpirationDate } } }');

            $complete_url = $api_url . '?query=' . $query;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $complete_url);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);  // in seconds
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);  // in seconds
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            $curl_result = curl_exec($ch);

            curl_close($ch);

            if ($curl_result === false) {
                throw new Exception('Could not connect to Kiva API, or timed out.');
            }

            // Parse the json result into a PHP object, then extract out
            // just the array of loan items.
            $result = json_decode($curl_result);

            if (isset($result->errors)) {
                $error_msg = $result->errors[0]->message;
                throw new Exception('Kiva API - ' . $error_msg);
            }

            $result = $result->data->loans->values;

            $cumulative = array_merge($cumulative, $result);

            if (count($result) === 0) {
                break;
            }
        }

        // Extract out those loans expiring within 24 hours.
        $time_limit = time() + (24 * 60 * 60);

        $expiring_loans = [];

        for ($idx = 0; $idx < count($cumulative); $idx++) {
            $expiry_str = $cumulative[$idx]->plannedExpirationDate;
            $expiry_time = strtotime($expiry_str);

            if ($expiry_time <= $time_limit) {
                $expiring_loans[] = $cumulative[$idx];
            }
        }

        return $expiring_loans;
    }
}
