<?php

namespace App\Services\Xml; 
use Illuminate\Support\Collection;
use SimpleXMLElement;
use Exception;
use Illuminate\Support\Facades\Log; 

class XmlGenerationService
{
    public function __construct(
        protected DateFormatterService $dateFormatter,
        protected MandatoryFieldChecker $fieldChecker,
        protected InstrumentService $instrumentService,
        protected XmlStorageService $storage,
        protected XmlValidationService $validator 
    ) {}

    public function generate(Collection $data): string
    {
        if ($data->isEmpty()) {
            throw new Exception('XML üçün məlumat yoxdur.');
        }

        $root = config('xml.root');
        $xml = new SimpleXMLElement("<{$root}></{$root}>");

        $header = $xml->addChild('HEADER');
        $header->addChild('USER-NAME', 'YourUserName');
        $header->addChild('UPLOADING-SOCIETY', 'AMANAT');
        $header->addChild('FILE-ID', 'File123');
        $header->addChild('ISO-CHAR-SET', 'ISO8859-1');

        $rightholders = $xml->addChild('RIGHTHOLDERS');

        foreach ($data as $index => $row) {
            $rowNumber = $index + 1;

            try {
                $localIdValue = $row['local_id'] ?? $row['Code'] ?? null;
                $lastNameValue = $row['last_name'] ?? $row['LastName'] ?? $row['Last Name'] ?? $row['surname'] ?? null;
                $countryResidenceValue = $row['country_of_residence'] ?? $row['CountryOfResidence'] ?? 'AZE';
                $firstNameValue = $row['first_name'] ?? $row['FirstName'] ?? $row['First Name'] ?? null;
                $genderValue = $row['gender'] ?? $row['Gender'] ?? $row['Sex'] ?? 'F';
                $ipnValue = $row['ipn'] ?? $row['IPN'] ?? null;
                $countryBirthValue = $row['country_of_birth'] ?? $row['CountryOfBirth'] ?? null;
                $pseudonymsValue = $row['Pseudonyms'] ?? $row['pseudonyms'] ?? null;
                $instrumentsValue = $row['Instruments'] ?? $row['instruments'] ?? $row['Instrument'] ?? $row['instrument'] ?? $row['Used Instruments'] ?? $row['Used_Instruments'] ?? $row['instrument_name'] ?? $row['Instrument Name'] ?? null;
                $performanceTypeValue = $row['Performance Types'] ?? $row['PerformanceTypes'] ?? $row['Performance Type'] ?? $row['performance_type'] ?? null;
                $rightsValue = $row['Rights'] ?? $row['rights'] ?? $row['RightCodes'] ?? null;
                $mandateTypeValue = $row['MandateType'] ?? $row['mandate_type'] ?? 'WW';
                $mandatedSocietyCode = $row['MandatedSocietyCode'] ?? '131';
                $mandatedSocietyName = $row['MandatedSocietyName'] ?? 'AMANAT';
                $dobValue = $row['dob'] ?? $row['DateOfBirth'] ?? $row['Date Of Birth'] ?? null;
                $mandateStartDateValue = $row['MandateStartDate'] ?? $row['Mandate Start Date'] ?? $row['mandate_start_date'] ?? null;
                $mandateEndDateValue = $row['MandateEndDate'] ?? $row['Mandate End Date'] ?? $row['mandate_end_date'] ?? null;
                $conditionalAspectsValue = $row['Conditional Aspects'] ?? $row['ConditionalAspects'] ?? $row['conditional_aspects'] ?? $row['Conditional Aspect'] ?? null;

                //Formatter
                $dobFormatted = $this->dateFormatter->formatExcelDate($dobValue, 'DATE-OF-BIRTH', $rowNumber);
                $mandateStartFormatted = $this->dateFormatter->formatExcelDate($mandateStartDateValue, 'MANDATE-START-DATE', $rowNumber);
                $mandateEndFormatted = $this->dateFormatter->formatExcelDate($mandateEndDateValue, 'MANDATE-END-DATE', $rowNumber);

                $localId = $this->fieldChecker->check($localIdValue, 'RIGHTHOLDER-LOCAL-ID', $rowNumber);
                $lastName = $this->fieldChecker->check($lastNameValue, 'RIGHTHOLDER-LAST-NAME', $rowNumber);
                $dob = $this->fieldChecker->check($dobFormatted, 'DATE-OF-BIRTH', $rowNumber);
                $countryResidence = $this->fieldChecker->check($countryResidenceValue, 'COUNTRY-OF-RESIDENCE', $rowNumber, 3);
                $mandateStart = $this->fieldChecker->check($mandateStartFormatted, 'MANDATE-START-DATE', $rowNumber);
                $mandateEnd = $this->fieldChecker->check($mandateEndFormatted, 'MANDATE-END-DATE', $rowNumber);


                //Build Xml
                $holder = $rightholders->addChild('RIGHTHOLDER');
                $holder->addChild('ACTION', 'INSERT');

                $this->addChildIfValue($holder, 'IPN', $ipnValue);
                $holder->addChild('RIGHTHOLDER-LOCAL-ID', htmlspecialchars(trim((string)$localId)));
                $holder->addChild('RIGHTHOLDER-FIRST-NAME', $this->safeValue($firstNameValue));
                $holder->addChild('RIGHTHOLDER-LAST-NAME', htmlspecialchars(trim((string)$lastName)));
                $holder->addChild('SEX', $genderValue);
                $holder->addChild('DATE-OF-BIRTH', htmlspecialchars(trim((string)$dob)));
                $this->addChildIfValue($holder, 'COUNTRY-OF-BIRTH', $countryBirthValue);
                $holder->addChild('COUNTRY-OF-RESIDENCE', htmlspecialchars(trim((string)$countryResidence)));

                $roles = $holder->addChild('IDENTIFYING-ROLES');
                $roles->addChild('IDENTIFYING-ROLE-CODE', 'SI');

                $this->addPseudonymsIfValue($holder, $pseudonymsValue);

                $this->instrumentService->addInstrumentsIfValue($holder, $instrumentsValue);

                $mandates = $holder->addChild('MANDATE-INFOS');
                $info = $mandates->addChild('MANDATE-INFO');
                $info->addChild('MANDATE-TYPE', $mandateTypeValue);
                $info->addChild('MANDATED-SOCIETY-CODE', $mandatedSocietyCode);
                $info->addChild('MANDATED-SOCIETY-NAME', $mandatedSocietyName);
                $params = $info->addChild('MANDATE-PARAMETERS');
                $param = $params->addChild('MANDATE-PARAMETER');
                $param->addChild('MANDATE-START-DATE', $mandateStart);
                $param->addChild('MANDATE-END-DATE', $mandateEnd);

                $this->addCodesIfValue($param, 'PERFORMANCE-TYPES', 'PERFORMANCE-TYPE-CODE', $performanceTypeValue);
                $this->addCodesIfValue($param, 'RIGHTS', 'RIGHT-CODE', $rightsValue);
                $this->addCodesIfValue($param, 'CONDITIONAL-ASPECTS', 'CONDITIONAL-ASPECT-CODE', $conditionalAspectsValue, 2);
            } catch (Exception $e) {
                Log::error("XML Sətr Emalı Xətası: " . $e->getMessage());
                throw $e;
            }
        }

        $filePath = $this->storage->saveAndGetPath($xml, $root);

        $result = $this->validator->validate($filePath);
        if (!$result['valid']) {
            throw new Exception('XML XSD-yə uyğun deyil: ' . implode('; ', $result['errors']));
        }

        return $filePath;
    }



    private function safeValue(?string $value): string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? '[NA]' : htmlspecialchars($trimmed);
    }

    private function addChildIfValue(SimpleXMLElement $parent, string $name, ?string $value): void
    {
        $trimmed = trim((string) $value);

        if ($trimmed !== '') {
            $parent->addChild($name, htmlspecialchars($trimmed));
        }
    }

    private function addPseudonymsIfValue(SimpleXMLElement $parent, ?string $value): void
    {
        $names = array_filter(array_map('trim', explode(',', (string) $value)));
        if (!empty($names)) {
            $pseudonyms = $parent->addChild('PSEUDONAMES');
            foreach ($names as $name) $pseudonyms->addChild('PSEUDONAME', htmlspecialchars($name));
        }
    }

    private function addCodesIfValue(SimpleXMLElement $parent, string $containerName, string $codeName, ?string $value, int $maxOccurs = 99): void
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
