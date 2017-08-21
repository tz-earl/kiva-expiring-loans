<!DOCTYPE html>
<html>
<head>
<style>
th {
  margin-right: .3em;
  border-bottom: .2em solid black;
}
td {
  margin-right: .3em;
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
print "<th>Loan ID</th><th>Expiry Date (GMT)</th><th>Loan Amount</th>\n";
print "</tr></head>\n";
print "<tbody>\n";

for ($idx = 0; $idx < $loan_cnt; $idx++) {
    $loan_id = $expiring[$idx]->id;
    $loan_expiry = $expiring[$idx]->plannedExpirationDate;
    $loan_expiry = strtotime($loan_expiry);
    $loan_expiry = gmdate('M j, Y g:ia', $loan_expiry);
    $loan_amt = $expiring[$idx]->loanAmount;

    print "<tr>\n";
    print "<td><a href='https://www.kiva.org/lend/$loan_id'>$loan_id</a></td>".
          "<td>$loan_expiry</td><td class='amount'>$loan_amt</td>\n";
    print "</tr>\n";
}

print "</tbody>\n";
print "</table>\n";

?>
</body>
</html>
