<!DOCTYPE html>
<html>
<head>
<style>
th {
  border-bottom: .2em solid black;
  padding: .5em;
}
td {
  border-bottom: .1em solid black;
  padding: 1em;
}
.amount {
  text-align: right;
}
</style>
</head>
<body>
<h3>Kiva Fundraising Loans that Expire Soon</h3>
<?php
require 'ExpiringLoans.php';

use Kiva\ExpiringLoans;

$exp_loans = new ExpiringLoans();
$expiring = $exp_loans->fetchExpiringLoans();

// Roughly order by expiring the soonest.
$expiring = array_reverse($expiring);

$loan_cnt = count($expiring);
print "<h4>Number of expiring loans is $loan_cnt </h4>\n";

$total_loan_amounts = $exp_loans->totalAmount();
$total_formatted = number_format($total_loan_amounts, 2, '.', ',');
print "<h4>Total amount of these loans is  $total_formatted </h4>\n";

print "<table>\n";
print "<thead><tr>\n";
print "<th>Loan ID</th><th>Expiry Date (GMT)</th><th>Loan Amount</th><th>Still Needed</th>\n";
print "</tr></head>\n";
print "<tbody>\n";

for ($idx = 0; $idx < $loan_cnt; $idx++) {
    $loan_id = $expiring[$idx]->id;

    // Convert the standard date string format as retrieved
    // into something a little more readable.
    $loan_expiry = $expiring[$idx]->plannedExpirationDate;
    $loan_expiry = strtotime($loan_expiry);
    $expiry_formatted = gmdate('M j, Y g:ia', $loan_expiry);

    $loan_amt = $expiring[$idx]->loanAmount;
    $amt_formatted = number_format($loan_amt, 2, '.', ',');

    $loan_funded = $expiring[$idx]->loanFundraisingInfo->fundedAmount;
    $loan_remaining = $loan_amt - $loan_funded;
    $remaining_formatted = number_format($loan_remaining, 2, '.', ',');

    print "<tr>\n";
    print "<td><a href='https://www.kiva.org/lend/$loan_id'>$loan_id</a></td>" .
          "<td>$expiry_formatted</td><td class='amount'>$amt_formatted</td>" .
          "<td class='amount'>$remaining_formatted</td>\n";
    print "</tr>\n";
}

print "</tbody>\n";
print "</table>\n";

?>
</body>
</html>
