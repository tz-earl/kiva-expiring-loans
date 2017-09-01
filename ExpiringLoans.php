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

define('GRAPHQL_URL', 'http://api.kivaws.org/graphql');
define('TEST_URL', 'testdata');
define('TEST_FILENAME', 'testdata.json');

class ExpiringLoans
{
    private $api_url = null;
    private $expiring_loans = null;

    public function __construct(string $url = '')
    {
        $this->api_url = $url ? $url : GRAPHQL_URL;
    }

    /**
     * Parse the incoming json into an object,
     * then extract just the array of loans.
     */
    private function parseOneBatch(string $json_data)
    {
        $result = json_decode($json_data);

        if (isset($result->errors)) {
            $error_msg = $result->errors[0]->message;
            throw new Exception('Kiva API - ' . $error_msg);
        }

        $loans = $result->data->loans->values;
        return $loans;
    }

    /**
     * Fetch all fundRaising loans using the GraphQL API.
     *
     * @return object[]
     */
    private function fetchLoansFromGraphQL()
    {
        $limit = 2000;  // No apparent problem handling this "batch" size for the query,
                        // but have had problems with 3500 producing an empty result set.

        $cumulative = [];

        // Fetch all loans that are fundRaising.
        for ($offset = 0;; $offset += $limit) {
            $query = urlencode('{ loans (filters: { status: fundRaising },' .
                " offset:$offset, limit: $limit, sortBy: newest)" .
                ' { totalCount values { id loanAmount plannedExpirationDate ' .
                '     loanFundraisingInfo { fundedAmount } } } }');

            $query_url = $this->api_url . '?query=' . $query;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $query_url);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);  // in seconds
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);  // in seconds
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            $curl_data = curl_exec($ch);

            curl_close($ch);

            if ($curl_data === false) {
                throw new Exception('Could not connect to Kiva API, or timed out.');
            }

            $loans = $this->parseOneBatch($curl_data);
            $cumulative = array_merge($cumulative, $loans);

            if (count($loans) < $limit) {
                break;
            }
        }

        return $cumulative;
    }

    /**
     * Fetch all fundraising loans from test data.
     *
     * @return object[]
     */
    private function fetchLoansFromTestData()
    {
        $testdata = file_get_contents(TEST_FILENAME);
        $loans = $this->parseOneBatch($testdata);
        return $loans;
    }

    /**
     * Fetch fundraising loans that expire within 24 hours.
     *
     * @return object[]
     */
    public function fetchExpiringLoans()
    {
        // Fetch all loans that are fundraising.
        switch ($this->api_url) {
            case GRAPHQL_URL:
                $fund_raising = $this->fetchLoansFromGraphQL();
                break;
            case TEST_URL:
                $fund_raising = $this->fetchLoansFromTestData();
                break;
            default:
                assert(false);
                break;
        }

        // Extract out those loans expiring within 24 hours.
        $time_limit = time() + (24 * 60 * 60);

        $expiring_loans = [];

        for ($idx = 0; $idx < count($fund_raising); $idx++) {
            $expiry_str = $fund_raising[$idx]->plannedExpirationDate;
            $expiry_time = strtotime($expiry_str);

            if ($expiry_time <= $time_limit) {
                $expiring_loans[] = $fund_raising[$idx];
            }
        }

        $this->expiring_loans = $expiring_loans;

        return $this->expiring_loans;
    }

    /**
     * Calculate the total loan amount of all loans that are expiring.
     *
     * @return int
     */
    public function totalAmount()
    {
        if (!$this->expiring_loans) {
            $this->fetchExpiringLoans();
        }

        $loan_cnt = count($this->expiring_loans);

        for ($idx = 0, $total = 0; $idx < $loan_cnt; $idx++) {
            $total += $this->expiring_loans[$idx]->loanAmount;
        }

        return $total;
    }
}
