<?php

return [

    /**
     * Regex for matching the record number.
     */
    'record-number' => env('RECORD_NUMBER', '/\d{1,4}\/(?:JP|HL|JL)\/20[0-5][0-9]|\d{1,4}\/20[0-5][0-9]\/(?:JP|HL|JL)/'),

    'remove-postfix' => ['/JP', '/JL'],
    /**
     * Regex for the document date.
     */
    'document-date' => env('DOCUMENT_DATE', '/[0-2][0-9]\.(?:\s*(?:Januar|Februar|März|April|Mai|Juni|August|September|Oktober|November|Dezember)\s*|[0-3][0-9]\.)20[0-5][0-9]/'),

];
