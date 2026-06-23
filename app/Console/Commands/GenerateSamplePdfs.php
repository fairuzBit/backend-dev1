<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tutor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateSamplePdfs extends Command
{
    protected $signature = 'app:generate-sample-pdfs';
    protected $description = 'Generate sample transcript and portfolio PDFs for testing OCR.';

    public function handle()
    {
        $tutors = Tutor::with('user')->get();

        if ($tutors->isEmpty()) {
            $this->error('No tutors found. Please run migrations and seeders first.');
            return;
        }

        $semesters = [3, 5, 7, 4, 6];

        foreach ($tutors as $index => $tutor) {
            $currentSemester = $semesters[$index % count($semesters)];
            $tutor->update(['current_semester' => $currentSemester]);

            $this->info("Generating Transcript for Tutor: {$tutor->user->name} (Semester {$currentSemester})");

            $html = "
                <style>
                    body { font-family: sans-serif; font-size: 14px; }
                    .header { text-align: center; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                </style>
                <div class='header'>
                    <h2>TRANSKRIP NILAI MAHASISWA</h2>
                    <p>Universitas Dian Nuswantoro</p>
                </div>
                <p><strong>Nama:</strong> {$tutor->user->name}</p>
                <p><strong>NIM:</strong> {$tutor->user->nim}</p>
                <p><strong>Program Studi:</strong> Teknik Informatika</p>
                <hr>
                <p>Mata Kuliah yang telah diselesaikan (Hingga Semester " . ($currentSemester - 1) . "):</p>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
            ";

            $totalSks = 0;
            $totalBobot = 0;
            $courses = [
                ['A11.111', 'Kalkulus', 4, 'A', 4],
                ['A11.112', 'Fisika', 3, 'B+', 3.5],
                ['A11.113', 'Dasar Pemrograman', 4, 'A', 4],
                ['A11.114', 'Matriks Ruang Vektor', 3, 'A', 4],
                ['A11.115', 'Matematika Diskrit', 3, 'B', 3],
                ['A11.116', 'Struktur Data', 4, 'A', 4],
                ['A11.117', 'Sistem Operasi', 3, 'A', 4],
                ['A11.118', 'Basis Data', 4, 'A', 4]
            ];

            // Assign courses based on semester (simulate transcript length)
            $courseCount = min(count($courses), ($currentSemester - 1) * 2);
            for ($i = 0; $i < $courseCount; $i++) {
                $c = $courses[$i];
                $totalSks += $c[2];
                $totalBobot += ($c[2] * $c[4]);
                $num = $i + 1;
                $html .= "<tr>
                            <td>{$num}</td>
                            <td>{$c[0]}</td>
                            <td>{$c[1]}</td>
                            <td>{$c[2]}</td>
                            <td>{$c[3]}</td>
                          </tr>";
            }

            $ipk = $totalSks > 0 ? number_format($totalBobot / $totalSks, 2) : "0.00";

            $html .= "
                    </tbody>
                </table>
                <br>
                <h4>Ringkasan IP Semester (IPS)</h4>
                <ul>
            ";
            
            // Generate dummy IPS for each semester
            $totalIps = 0;
            for ($s = 1; $s < $currentSemester; $s++) {
                // Random IPS between 3.20 and 4.00
                $ips = mt_rand(320, 400) / 100;
                $totalIps += $ips;
                $html .= "<li>Semester {$s}: <strong>" . number_format($ips, 2) . "</strong></li>";
            }
            
            $computedIpk = ($currentSemester - 1) > 0 ? number_format($totalIps / ($currentSemester - 1), 2) : "0.00";

            $html .= "
                </ul>
                <hr>
                <p><strong>Total SKS:</strong> {$totalSks}</p>
                <p><strong>Indeks Prestasi Kumulatif (IPK) Otomatis:</strong> {$computedIpk}</p>
                <p><i>Dokumen ini dicetak secara otomatis dan sah. (Mengandung IPS per semester untuk dihitung OCR)</i></p>
            ";

            $pdf = Pdf::loadHTML($html);
            $fileName = "transcript_" . str_replace(' ', '_', strtolower($tutor->user->name)) . "_sem{$currentSemester}.pdf";
            Storage::disk('public')->put("sample_documents/{$fileName}", $pdf->output());
            $this->line("Created: public/storage/sample_documents/{$fileName}");
            
            // Create portfolio for the first 2 tutors
            if ($index < 2) {
                $portHtml = "
                    <style>
                        body { font-family: sans-serif; font-size: 14px; }
                        .header { text-align: center; margin-bottom: 20px; }
                    </style>
                    <div class='header'>
                        <h2>PORTOFOLIO TUTOR</h2>
                        <p>KonekDin Platform</p>
                    </div>
                    <p><strong>Nama:</strong> {$tutor->user->name}</p>
                    <p><strong>Keahlian:</strong> Web Development, Database Architecture</p>
                    <hr>
                    <h3>Pengalaman Mengajar</h3>
                    <ul>
                        <li>Asisten Praktikum Basis Data (1 Tahun)</li>
                        <li>Mentor Bootcamp Web Programming</li>
                    </ul>
                    <h3>Proyek Unggulan</h3>
                    <ul>
                        <li>Sistem Informasi Akademik Kampus (Laravel & Vue.js)</li>
                        <li>Aplikasi Mobile E-Commerce (Flutter)</li>
                    </ul>
                ";
                
                $portPdf = Pdf::loadHTML($portHtml);
                $portFileName = "portfolio_" . str_replace(' ', '_', strtolower($tutor->user->name)) . ".pdf";
                Storage::disk('public')->put("sample_documents/{$portFileName}", $portPdf->output());
                $this->line("Created: public/storage/sample_documents/{$portFileName}");
            }
        }

        // Generate dummy transcript for Budi Santoso (Learner 6) who is currently applying
        $this->info("Generating Transcript for Pending Applicant: Budi Santoso");
        $pendingHtml = "
            <style>
                body { font-family: sans-serif; font-size: 14px; }
                .header { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            </style>
            <div class='header'>
                <h2>TRANSKRIP NILAI MAHASISWA (PENDAFTAR)</h2>
                <p>Universitas Dian Nuswantoro</p>
            </div>
            <p><strong>Nama:</strong> Budi Santoso</p>
            <p><strong>NIM:</strong> A11.2023.10006</p>
            <p><strong>Program Studi:</strong> Teknik Informatika</p>
            <hr>
            <table>
                <thead>
                    <tr><th>No</th><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th></tr>
                </thead>
                <tbody>
                    <tr><td>1</td><td>A11.111</td><td>Kalkulus</td><td>4</td><td>A</td></tr>
                    <tr><td>2</td><td>A11.113</td><td>Dasar Pemrograman</td><td>4</td><td>A</td></tr>
                </tbody>
            </table>
            <h4>Ringkasan IP Semester (IPS)</h4>
            <ul>
                <li>Semester 1: <strong>3.95</strong></li>
                <li>Semester 2: <strong>3.80</strong></li>
            </ul>
            <hr>
            <p><strong>Total SKS:</strong> 8</p>
            <p><strong>Indeks Prestasi Kumulatif (IPK) Otomatis:</strong> 3.88</p>
            <p><i>Dokumen ini dicetak untuk pendaftaran tutor.</i></p>
        ";
        
        $pendingPdf = Pdf::loadHTML($pendingHtml);
        Storage::disk('public')->makeDirectory('transcripts');
        Storage::disk('public')->put("transcripts/dummy_learner6.pdf", $pendingPdf->output());
        $this->line("Created: public/storage/transcripts/dummy_learner6.pdf");

        $this->info('All sample PDFs generated successfully!');
    }
}
