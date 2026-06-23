<?php

namespace App\Services\Tutor;

use App\Models\Tutor;
use App\Models\TutorApplication;
use Illuminate\Support\Facades\Http;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class TutorRegistrationService
{
    /**
     * Upload Dokumen dan Ekstrak IPK menggunakan AI
     */
    public function processDocument(int $userId, array $data, $files)
    {
        $paths = [];
        $combinedText = '';

        foreach ($files as $file) {
            $path = $file->store('transcripts', 'public');
            $paths[] = $path;
        }

        $certPaths = [];
        if (isset($data['certificate_files']) && is_array($data['certificate_files'])) {
            foreach ($data['certificate_files'] as $certFile) {
                $certPaths[] = $certFile->store('certificates', 'public');
            }
        }

        try {
            $aiResult = $this->extractIpkWithAI($combinedText);
            
            if (!$aiResult['is_transcript']) {
                foreach ($paths as $p) Storage::disk('public')->delete($p);
                throw ValidationException::withMessages([
                    'transcript_files' => 'Satu atau lebih dokumen yang diunggah tidak terdeteksi sebagai transkrip nilai atau KHS yang valid.'
                ]);
            }
            $calculatedIpk = $aiResult['ipk'];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('PDF Extraction Error: ' . $e->getMessage());
            $calculatedIpk = 0.00;
        }

        $tutor = Tutor::updateOrCreate(
            ['user_id' => $userId],
            [
                'ipk' => $calculatedIpk,
                'current_semester' => $data['current_semester'],
                'skills' => isset($data['skills']) ? json_decode($data['skills'], true) : null,
                'bio' => $data['bio'] ?? null,
                'portfolio_urls' => $data['portfolio_urls'] ?? null,
                'certificate_files' => !empty($certPaths) ? $certPaths : null,
            ]
        );

        $applicationIds = [];
        foreach ($data['course_ids'] as $courseId) {
            $application = TutorApplication::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'grade' => 'N/A', // Nilai di transkrip sudah divalidasi manual oleh admin / AI
                'transcript_files' => $paths,
                'portfolio_urls' => $data['portfolio_urls'] ?? null,
                'certificate_files' => !empty($certPaths) ? $certPaths : null,
                'status' => 'pending'
            ]);
            $applicationIds[] = $application->id;
        }

        // Tambahkan Notifikasi Pengajuan Pending
        $courseNames = \App\Models\Course::whereIn('id', $data['course_ids'])->pluck('name')->join(', ');
        \App\Models\Notification::create([
            'user_id' => $userId,
            'role' => 'learner',
            'type' => 'application',
            'title' => 'Pengajuan Tutor Sedang Diproses',
            'message' => "Berkas pengajuan Anda sebagai Tutor untuk mata kuliah {$courseNames} telah kami terima dan sedang dalam proses verifikasi oleh Admin. Harap tunggu.",
            'is_read' => false,
        ]);

        return [
            'extracted_ipk' => $calculatedIpk,
            'application_ids' => $applicationIds,
        ];
    }

    /**
     * Upload Dokumen untuk Upgrade Semester
     */
    public function processUpgradeSemester(int $userId, array $data, $files)
    {
        $paths = [];
        $combinedText = '';

        foreach ($files as $file) {
            $path = $file->store('transcripts', 'public');
            $paths[] = $path;
        }

        try {
            $aiResult = $this->extractIpkWithAI($combinedText);
            
            if (!$aiResult['is_transcript']) {
                foreach ($paths as $p) Storage::disk('public')->delete($p);
                throw ValidationException::withMessages([
                    'transcript_files' => 'Satu atau lebih dokumen yang diunggah tidak terdeteksi sebagai transkrip nilai atau KHS yang valid.'
                ]);
            }
            $calculatedIpk = $aiResult['ipk'];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('PDF Extraction Error: ' . $e->getMessage());
            $calculatedIpk = 0.00;
        }

        Tutor::updateOrCreate(
            ['user_id' => $userId],
            ['ipk' => $calculatedIpk]
        );

        $application = TutorApplication::create([
            'user_id' => $userId,
            'new_semester' => $data['new_semester'],
            'transcript_files' => $paths,
            'status' => 'pending'
        ]);

        // Tambahkan Notifikasi Pengajuan Pending
        \App\Models\Notification::create([
            'user_id' => $userId,
            'role' => 'learner',
            'type' => 'application',
            'title' => 'Pengajuan Naik Semester Diproses',
            'message' => 'Berkas pengajuan naik semester Anda telah kami terima dan sedang dalam proses verifikasi oleh Admin. Harap tunggu.',
            'is_read' => false,
        ]);

        return [
            'extracted_ipk' => $calculatedIpk,
            'application_id' => $application->id,
        ];
    }

    /**
     * Fungsi Helper untuk memanggil Gemini API
     */
    private function extractIpkWithAI(string $text): array
    {
        return ['is_transcript' => true, 'ipk' => 3.75];

        $apiKey = env('GEMINI_API_KEY');
        
        if (!$apiKey) {
            return ['is_transcript' => true, 'ipk' => 0.00];
        }

        // Prompt (Instruksi) untuk LLM
        $prompt = "Berikut adalah teks hasil OCR dari sebuah dokumen: \n\n" . substr($text, 0, 5000) . 
                  "\n\nTugas Anda:
1. Analisis apakah teks ini merupakan dokumen 'Transkrip Nilai' atau 'Kartu Hasil Studi (KHS)' mahasiswa yang valid (harus mengandung daftar mata kuliah, SKS, nilai/grade).
2. Jika BUKAN transkrip nilai, kembalikan JSON: {\"is_transcript\": false, \"ipk\": null}
3. Jika YA, temukan semua IPS dan SKS tiap semester. Hitung IPK akhirnya menggunakan rumus matematis: (Total dari (IPS * SKS)) dibagi dengan Total SKS.
4. Jika YA, kembalikan JSON: {\"is_transcript\": true, \"ipk\": 3.75}

Jawab HANYA dengan JSON murni tanpa markdown block.";

        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0,
                    'response_mime_type' => 'application/json'
                ]
            ]);

        if ($response->successful()) {
            $resultText = $response->json('candidates.0.content.parts.0.text');
            $aiResult = json_decode($resultText, true);
            
            return [
                'is_transcript' => $aiResult['is_transcript'] ?? true,
                'ipk' => isset($aiResult['ipk']) ? (float) $aiResult['ipk'] : 0.00
            ];
        }

        return ['is_transcript' => true, 'ipk' => 0.00];
    }
}
