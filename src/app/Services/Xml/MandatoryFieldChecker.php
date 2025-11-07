<?php

namespace App\Services\Xml;

use Exception;

class MandatoryFieldChecker
{
    private const LOCAL_ID_COLUMNS = [
        'local_id',
        'Code',
        'OwnerCode',
        'ID',
        'id',
        'MemberID',
        'ClientCode'
    ];
    
    public function check(
        ?string $value, 
        string $fieldName, 
        int $rowNumber, 
        ?int $expectedLength = null
    ): string {
        $trimmed = trim((string) $value);
        
        if ($trimmed === '' || $trimmed === '[NA]') {
            if ($fieldName === 'RIGHTHOLDER-LOCAL-ID') {
                 return 'LOCAL-ID-QEYRI-MUEYYEN-' . time() . '-' . $rowNumber; 
            }
            
            $columnList = implode("', '", self::LOCAL_ID_COLUMNS);
            $errorMessage = "Sətr $rowNumber: Məcburi sahə '$fieldName' boş ola bilməz.";
            
            if ($fieldName === 'RIGHTHOLDER-LOCAL-ID') {
                 $errorMessage .= " Excel-də ID sütunu başlığı bu adlardan biri olmalıdır: ['" . $columnList . "'].";
            }
            
            throw new Exception($errorMessage);
        }

        if ($expectedLength !== null && strlen($trimmed) !== $expectedLength) {
            throw new Exception("Sətr $rowNumber: '$fieldName' tam olaraq $expectedLength simvol olmalıdır. Dəyər: '$trimmed'.");
        }

        return $trimmed;
    }
}