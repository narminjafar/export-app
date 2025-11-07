<?php

namespace App\Services\Xml;

use DateTime;
use Exception;

class DateFormatterService
{
  
    public function formatExcelDate(?string $value, string $fieldName, int $rowNumber): ?string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '' || $trimmed === '[NA]') return null;

        if (is_numeric($trimmed) && $trimmed > 1) {
            try {
                $unixTimestamp = ($trimmed - 25569) * 86400; 
                $date = new DateTime("@" . $unixTimestamp);
                return $date->format('Y-m-d');
            } catch (Exception $e) {
                throw new Exception("Row $rowNumber: The value ('$trimmed') in '$fieldName' is not a valid Excel serial date number.");
            }
        }
        
        try {
            $date = new DateTime($trimmed);
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return null; 
        }
    }
}