PHP-Zoho
========

PHP client library to the Zoho.com API.

This is a work in progress, however you are most welcome to use and contribute back. Currently has partial support for ZohoCRM, ZohoCreator, and ZohoReports. I'll add features as I need them or others request them. Use at your own risk!

Example usage:

include('PHP-Zoho/zoho.php');

// Instantiate a ZohoCreator object
$zohoCreator = new ZohoCreator($myAuthToken);

// List your applications
$zohoCreator->applications();

// List the forms & views in your application
$zohoCreator->application($myApplicationName)->formsAndViews();

// Add a record
$zohoCreator->application($myApplicationName)->add($myTableName, array($myFieldName => $myFieldValue));

// View records in a view
$zohoCreator->application($myApplicationName)->viewRecords($myViewName);

// Instantiate a ZohoCRM object
$zohoCrm = new ZohoCrm($myAuthToken);

// Get all the records in a module
$zohoCrm->module($myModuleName)->getRecords($myOptionsArray)

// Instantiate a ZohoReports object
$zohoReports = new ZohoReports($myUsername, $myPassword, $myApiKey);

// List your databases
$zohoReports->databases();

// Import a CSV into a table. See code for extra options.
$zohoReports->database($myDatabase)->importCsv($myTableName, $myCsvFilePath);

// Export a table. See code for extra options.
$zohoReports->database($myDatabase)->export($myTableName);


