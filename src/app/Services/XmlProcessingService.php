<?php

namespace App\Services;

use App\Services\Xml\XmlGenerationService;
use App\Services\Xml\XmlValidationService;
use Illuminate\Support\Facades\Log;

class XmlProcessingService
{
    protected ExcelProcessingService $excelService;
    protected XmlGenerationService $xmlService;
    protected XmlValidationService $validationService;

    public function __construct(
        ExcelProcessingService $excelService,
        XmlGenerationService $xmlService,
        XmlValidationService $validationService
    ) {
        $this->excelService = $excelService;
        $this->xmlService = $xmlService;
        $this->validationService = $validationService;
    }

    public function process($file): array
    {
        return rescue(function () use ($file) {
            $excel = $this->excelService->process($file);

            $xmlFile = $this->xmlService->generate($excel['rows']);

            return [
                'success' => true,
                'message' => 'XML uğurla yaradıldı və XSD yoxlamasından keçdi.',
                'file' => $xmlFile
            ];
        }, function (\Throwable $e) {
            Log::error('XML işlənməsi zamanı xəta: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        });
    }
}
