<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadXlsxRequest;
use App\Models\UploadLog;
use App\Services\ExcelProcessingService;
use App\Services\XmlGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    protected $excelService;
    protected $xmlService;

    public function __construct(ExcelProcessingService $excelService, XmlGenerationService $xmlService)
    {
        $this->excelService = $excelService;
        $this->xmlService = $xmlService;
    }

    public function upload(UploadXlsxRequest $request)
    {
        $file = $request->file('file');

        $userId = auth()->id();

        $log = UploadLog::create([
            'file_name' => $file->getClientOriginalName(),
            'uploaded_by' => $userId,
            'status' => 'pending',
        ]);

        try {
            $result = $this->excelService->process($file);

            $dataRows = $result['rows'];

            $filename = $this->xmlService->generate($dataRows);

            $log->update([
                'status' => 'success',
                'message' => 'XML uğurla yaradıldı',
            ]);

            Log::info("{$userId} üçün XML faylı uğurla yaradıldı: {$filename}.");

            return response()->json([
                'success' => true,
                'message' => 'XML yaradıldı',
                'download_url' => route('download.file', ['file' => $filename])
            ]);
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);

            Log::error("Fayl emalı zamanı səhv baş verdi ({$userId}): " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function downloadFile(string $file)
    {

        if (!Storage::disk('local')->exists($file)) {
            abort(404, "Fayl tapılmadı.");
        }

        $fileName = basename($file);

        return Storage::disk('local')->download($file, $fileName, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
