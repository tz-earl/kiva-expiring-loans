# kiva-expiring-loans

This is an exercise to create a script for Kiva Microfunds that queries
their GraphQL API for loans.

Filter for loans that have a status of fundRaising and a plannedExpirationDate
in the next 24 hours. Use the loan amount property on those loans to determine
the total dollar amount it would take to fund all of these loans and return or
display that as a result. Show also a link to each loan and the amount it has
left to fundraise.
