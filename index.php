<?php
require_once("../includes/php/dsn.php");
require ("google-sheets-api/vendor/autoload.php");

putenv('GOOGLE_APPLICATION_CREDENTIALS=credenciales.json');
$spreadsheetId = "1wxoxFgFnUBNbUrhRG7FC5u0VnqU7cUclmtsXrqpR9TY";
$range = "FACTURAS!A2:F";
$valueInputOption = 'RAW';

function appendValues($spreadsheetId, $range, $valueInputOption,$dbifx)
{
    $queryArticulo=$dbifx->query("SELECT
    cc0_provee, cc0_tipinc, cc0_numero, cc0_codmon, cc0_contab, cc0_impsal
    FROM
    cc0, cc1
    WHERE
    cc0_numcmp = cc1_numcmp AND
    cc0_impsal > 0 AND cc1_vtocal <= TODAY+3");
    $data = $queryArticulo->fetchAll(PDO::FETCH_NUM);
    //print_r($data);

    /* Load pre-authorized user credentials from the environment.
       TODO(developer) - See https://developers.google.com/identity for
        guides on implementing OAuth2 for your application. */
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/spreadsheets');
    $client->setAccessType('offline');
    $service = new Google_Service_Sheets($client);
    try{
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $data
        ]);

        $params = [
            'valueInputOption' => $valueInputOption
        ];
        $result = $service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
        printf("%d cells updated.", $result->getUpdatedCells());

    } catch (Exception $e) {
        // TODO(developer) - handle error appropriately
        echo 'Message: ' . $e->getMessage();
    }
}

appendValues($spreadsheetId, $range, $valueInputOption,$dbifx);

?>