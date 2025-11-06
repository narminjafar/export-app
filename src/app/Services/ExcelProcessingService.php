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
                return $rows->map(function ($row) {
                    return $row->map(fn($cell) => is_string($cell) ? trim($cell) : $cell);
                })->filter(fn($row) => $row->filter()->isNotEmpty());
            }
        }, $file);

        $collection = $collections->first();

        if (!$collection || $collection->isEmpty()) {
            throw new \Exception('Excel faylı boşdur və ya düzgün oxunmadı.');
        }

        $headers = $collection->first();
        if (!$headers) {
            throw new \Exception('Excel faylında başlıqlar tapılmadı.');
        }

        $dataRows = $collection->slice(1)->map(function ($row) use ($headers) {
            return $headers->combine($row);
        });

        return [
            'headers' => $headers,
            'rows' => $dataRows
        ];
    }
}
