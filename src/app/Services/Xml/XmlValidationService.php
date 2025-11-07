<?php

namespace App\Services\Xml;

use DOMDocument;
use Illuminate\Support\Facades\Storage;

class XmlValidationService
{
    public function validate(string $xmlPath, ?string $xsdPath = null): array
    {
        $xsdPath ??= base_path('app/xsd/ipd4.xsd');

        if (!Storage::disk('local')->exists($xmlPath)) {
            throw new \Exception("XML file not found:  {$xmlPath}");
        }

        if (!file_exists($xsdPath)) {
            throw new \Exception("XSDfile not found:  {$xsdPath}");
        }

        $xmlContent = Storage::disk('local')->get($xmlPath);
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);

        libxml_use_internal_errors(true);
        $isValid = $dom->schemaValidate($xsdPath);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return [
            'valid' => $isValid,
            'errors' => $isValid ? [] : collect($errors)->map(function ($error) {
                return trim($error->message);
            })->values()->toArray(),
        ];
    }
}
