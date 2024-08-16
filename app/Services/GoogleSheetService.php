<?php
namespace App\Services;

class GoogleSheetService
{
    public function createSheet($service, $spreadSheetId, $sheetName)
    {
        $sheetsCreateRequest = array(
            'requests' => array(
                array(
                    'addSheet' => array(
                        'properties' => array(
                            'title' => $sheetName
                        ),
                    ),
                ),
            )
        );
        $body = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest($sheetsCreateRequest);
        $service->spreadsheets->batchUpdate($spreadSheetId, $body);
    }

    public function clearSheets($service, $spreadSheetId, $sheets)
    {
        $requestBody = new \Google_Service_Sheets_BatchClearValuesRequest();
        $requestBody->setRanges($sheets);
        $service->spreadsheets_values->batchClear($spreadSheetId, $requestBody);
    }

    public function getSpreadSheetTitles($service, $spreadSheetId)
    {
        $sheetsInfo = $service->spreadsheets->get($spreadSheetId);
        $sheets = $sheetsInfo->getSheets();
        $sheetProperties = array_column($sheets, 'properties');
        $sheetsData = [];
        foreach($sheetProperties as $properties) {
            $sheetsData[$properties->getSheetId()] = $properties->getTitle();
        }
        return array_values($sheetsData);
    }

    public function prepareSheetData($range, $records, $columns)
    {
        $values = array_map(function($insert) {
            return array_values((array) $insert);
        }, $records->toArray());
        array_unshift($values, $columns);
        return new \Google_Service_Sheets_ValueRange([
            'range' => $range,
            'values' => $values,
        ]);
    }
}