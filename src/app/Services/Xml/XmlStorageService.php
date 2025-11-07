<?php

namespace App\Services\Xml;

use SimpleXMLElement;
use DOMDocument;
use Illuminate\Support\Facades\Storage;

class XmlStorageService
{

    public function saveAndGetPath(SimpleXMLElement $xml, string $root): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        $filePath = 'xml_exports/' . $root . '_' . time() . '.xml';
        
        $xmlContent = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $dom->saveXML());
        
        Storage::disk('local')->put($filePath, $xmlContent);

        return $filePath;
    }
}