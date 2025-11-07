<?php

namespace App\Services\Xml;

use SimpleXMLElement;

class InstrumentService
{
    private const INSTRUMENT_ENUM = [
        'KEYBOARDS',
        'MECHANICAL',
        'PERCUSSION',
        'STRINGS',
        'TUNED PERC',
        'VOCAL',
        'WIND',
        'NUM-OF-PERFORMERS'
    ];
    

    public function addInstrumentsIfValue(SimpleXMLElement $parent, ?string $value): void
    {
        $trimmedValue = trim((string) $value);
        if ($trimmedValue === '') return;

        $instrumentsRaw = preg_split('/[,\/]/', $trimmedValue);
        $instrumentsList = array_filter(array_map('trim', $instrumentsRaw));

        if (!empty($instrumentsList)) {
            $instruments = $parent->addChild('INSTRUMENTS');
            foreach ($instrumentsList as $instrument) {
                $type = strtoupper(trim($instrument));
                
                if (!in_array($type, self::INSTRUMENT_ENUM)) $type = 'VOCAL'; 
                
                $element = $instruments->addChild('INSTRUMENT');
                $element->addChild('INSTRUMENT-TYPE', htmlspecialchars($type));
                $element->addChild('INSTRUMENT-INFO', ''); 
            }
        }
    }
}