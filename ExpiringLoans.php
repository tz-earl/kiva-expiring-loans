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
        $api_url = 'http://api.kivaws.org/graphql';

        $limit = 1000;  // No apparent problem handling this "batch" size for the query,
                        // but have had problems with 3500 producing in an empty result set.

        $cumulative = [];

        for ($offset = 0; ; $offset += $limit) {

            $query = urlencode("{ loans (filters: { status: fundRaising }, offset:$offset, limit: $limit, sortBy: newest)" .
                                        ' { totalCount values { id loanAmount plannedExpirationDate } } }');

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
                throw new Exception('Could not connect to Kiva, or timed out.');
            }

            // Parse the json result into a PHP object, then extract out
            // just the array of loan items.
            $result = json_decode($curl_result);
            $result = $result->data->loans->values;

            $cumulative = array_merge($cumulative, $result);

            if (count($result) === 0) {
                break;
            }
        }

        return $cumulative;
    }
}
