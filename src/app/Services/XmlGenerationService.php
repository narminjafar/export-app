<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use DOMDocument;

class XmlGenerationService
{
    public function generate(Collection $data): string
    {
        $xml = new SimpleXMLElement('<IPD4upload xmlns="http://www.your-xsd-namespace.com/ipd4"/>');

        foreach ($data as $row) {
            $row = $row->mapWithKeys(function ($value, $key) {
                $key = trim($key);
                $key = \Illuminate\Support\Str::snake($key);
                return [$key => $value];
            });

            $holder = $xml->addChild('IPRHolder');

            foreach ($row as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                if (in_array($key, ['pseudonyms', 'instruments'])) {
                    $parentTag = ucfirst($key); 
                    $parent = $holder->addChild($parentTag);
                    foreach (explode(',', $value) as $item) {
                        $childTag = $key === 'pseudonyms' ? 'Pseudonym' : 'Instrument';
                        $parent->addChild($childTag, trim($item));
                    }
                    continue;
                }

                $xmlTag = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                $holder->addChild($xmlTag, (string) $value);
            }
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $formattedXml = $dom->saveXML();

        $xmlFileName = 'xml_exports/IPD4upload_' . time() . '.xml';
        Storage::disk('local')->put($xmlFileName, $formattedXml);

        return $xmlFileName;
    }
}
