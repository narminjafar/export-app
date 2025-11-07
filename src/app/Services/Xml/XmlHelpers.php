<?php
namespace App\Services\Xml;

use SimpleXMLElement;

trait XmlHelpers
{
    protected function addChildIfValue(SimpleXMLElement $parent, string $name, ?string $value): void
    {
        $trimmed = trim((string) $value);
        if ($trimmed !== '') {
            $parent->addChild($name, htmlspecialchars($trimmed));
        }
    }

    protected function addPseudonymsIfValue(SimpleXMLElement $parent, ?string $value): void
    {
        $names = array_filter(array_map('trim', explode(',', (string) $value)));
        if (!empty($names)) {
            $pseudonyms = $parent->addChild('PSEUDONAMES');
            foreach ($names as $name) $pseudonyms->addChild('PSEUDONAME', htmlspecialchars($name));
        }
    }

    protected function addCodesIfValue(SimpleXMLElement $parent, string $containerName, string $codeName, ?string $value, int $maxOccurs = 99): void
    {
        $codes = [];
        $trimmed = trim((string) $value);
        if ($trimmed !== '') {
            $codes = array_filter(array_map('trim', preg_split('/[,\/]/', $trimmed)));
        }

        if (empty($codes)) return;

        if (count($codes) > $maxOccurs) {
            $codes = array_slice($codes, 0, $maxOccurs);
        }

        $container = $parent->addChild($containerName);
        foreach ($codes as $code) {
            $container->addChild($codeName, htmlspecialchars(strtoupper($code)));
        }
    }
}
