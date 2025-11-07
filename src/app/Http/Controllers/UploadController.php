<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadXlsxRequest;
use App\Models\UploadLog;
use App\Services\XmlProcessingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    protected XmlProcessingService $xmlProcessing;

    public function __construct(XmlProcessingService $xmlProcessing)
    {
        $this->xmlProcessing = $xmlProcessing;
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

        $result = $this->xmlProcessing->process($file);

        $log->update([
            'status' => $result['success'] ? 'success' : 'failed',
            'message' => $result['message'],
        ]);

        return response()->json($result, $result['success'] ? 200 : 422);
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
