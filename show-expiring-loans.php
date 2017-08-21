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
include 'ExpiringLoans.php';

use Kiva\ExpiringLoans;

$exp_loans = new ExpiringLoans;
$expiring = $exp_loans->fetchExpiringLoans();

// Roughly order by expiring the soonest.
$expiring = array_reverse($expiring);

$loan_cnt = count($expiring);
print "<h4>Number of expiring loans is $loan_cnt </h4>\n";

for ($idx = 0, $total_loan_amounts = 0; $idx < $loan_cnt; $idx++ ) {
  $total_loan_amounts += $expiring[$idx]->loanAmount;
}
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
    $loan_expiry = gmdate('M j, Y g:ia', $loan_expiry);

    $loan_amt = $expiring[$idx]->loanAmount;

    $loan_funded = $expiring[$idx]->fundedAmount;
    $loan_remaining = $loan_amt - $loan_funded;
    $remaining_formatted = number_format($loan_remaining, 2, '.', ',');

    print "<tr>\n";
    print "<td><a href='https://www.kiva.org/lend/$loan_id'>$loan_id</a></td>" .
          "<td>$loan_expiry</td><td class='amount'>$loan_amt</td>" .
          "<td class='amount'>$remaining_formatted</td>\n";
    print "</tr>\n";
}

print "</tbody>\n";
print "</table>\n";

?>
</body>
</html>
