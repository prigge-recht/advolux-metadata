<?php

return [

    /**
     * Regex for matching the record number.
     */
    'record-number' => env('RECORD_NUMBER', '/\d{1,3}\/20[0-5][0-9]\/(?:JP|HL|JL)/'),

    'lawyers' => ['JP', 'JL'],
    /**
     * Regex for the document date.
     */
    'document-date' => env('DOCUMENT_DATE', '/[0-2][0-9]\.[0-3][0-9]\.20[0-5][0-9]/'),

];
