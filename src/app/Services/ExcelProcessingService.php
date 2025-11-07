<?php

namespace App\Services;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ExcelProcessingService
{
    public function process($file): array
    {
        $collections = Excel::toCollection(new class implements ToCollection {
            public function collection(Collection $rows)
            {
                return $rows
                    ->map(fn($row) => $row->map(fn($cell) => is_string($cell) ? trim($cell) : $cell))
                    ->filter(fn($row) => $row->filter()->isNotEmpty());
            }
        }, $file);

        $collection = $collections->first() ?? throw new \Exception('Excel faylı oxuna bilmədi.');

        $headers = $collection->first() ?? throw new \Exception('Başlıqlar tapılmadı.');

        $dataRows = $collection->slice(1)->map(fn($row) => $headers->combine($row));

        return [
            'headers' => $headers,
            'rows' => $dataRows->isNotEmpty() 
                ? $dataRows 
                : throw new \Exception('Excel-də məlumat sətri yoxdur.')
        ];
    }
}
