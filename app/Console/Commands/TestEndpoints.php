<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Http;

class TestEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-endpoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all API endpoints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $admin = User::where('role', 'admin')->first();
        $tutor = User::whereHas('tutor')->first();
        $learner = User::where('role', 'learner')->first();

        if (!$admin || !$tutor || !$learner) {
            $this->error("Missing users!");
            return;
        }

        $adminToken = JWTAuth::fromUser($admin);
        $tutorToken = JWTAuth::fromUser($tutor);
        $learnerToken = JWTAuth::fromUser($learner);

        $baseUrl = 'http://localhost:8000/api';

        $testEndpoint = function($method, $uri, $token = null, $data = []) use ($baseUrl) {
            $url = $baseUrl . $uri;
            
            $req = Http::withHeaders(['Accept' => 'application/json']);
            if ($token) {
                $req = $req->withToken($token);
            }
            
            try {
                if ($method === 'get') {
                    $res = $req->get($url, $data);
                } else {
                    $res = $req->$method($url, $data);
                }
                
                $status = $res->status();
                $this->info(str_pad($method, 6) . " " . str_pad($uri, 40) . " => " . $status);
                if ($status >= 400 && $status != 404 && $status != 405) {
                    $this->error("   Error: " . substr($res->body(), 0, 150));
                }
            } catch (\Exception $e) {
                $this->error("   Exception: " . $e->getMessage());
            }
        };

        $this->info("\n--- PUBLIC ROUTES ---");
        $testEndpoint('post', '/login', null, ['email' => $admin->email, 'password' => 'password']);
        
        $testEmail = '111user' . rand(1000, 9999) . '@mhs.dinus.ac.id';
        $testEndpoint('post', '/register', null, [
            'fullName' => 'Mahasiswa Baru',
            'email' => $testEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nim' => '111.2023.' . rand(1000, 9999),
            'university' => 'Universitas Dian Nuswantoro',
            'major' => 'Teknik Informatika',
        ]);

        $this->info("\n--- ADMIN ROUTES ---");
        $testEndpoint('get', '/admin/users', $adminToken);
        $testEndpoint('get', '/admin/applications', $adminToken);
        $testEndpoint('get', '/admin/moderation/reviews', $adminToken);
        $testEndpoint('get', '/admin/payments', $adminToken);

        $this->info("\n--- TUTOR ROUTES ---");
        $testEndpoint('get', '/tutor/dashboard', $tutorToken);
        $testEndpoint('get', '/tutor/availability', $tutorToken);
        $testEndpoint('get', '/tutor/bookings', $tutorToken);
        $testEndpoint('get', '/tutor/schedules', $tutorToken);
        $testEndpoint('get', '/tutor/history', $tutorToken);
        $testEndpoint('get', '/tutor/reviews', $tutorToken);
        $testEndpoint('get', '/tutor/notifications', $tutorToken);

        $this->info("\n--- LEARNER ROUTES ---");
        $testEndpoint('get', '/tutors', $learnerToken);
        $testEndpoint('get', '/me', $learnerToken);
        $testEndpoint('get', '/dashboard', $learnerToken);
        $testEndpoint('get', '/learner/bookings', $learnerToken);
        $testEndpoint('get', '/schedules', $learnerToken);
        $testEndpoint('get', '/learner/history', $learnerToken);
        $testEndpoint('get', '/learner/notification', $learnerToken);

        $this->info("\n--- AUTHENTICATED SHARED ROUTES ---");
        // Create a separate token just for logout so we don't invalidate our main test tokens
        $logoutToken = JWTAuth::fromUser($admin);
        $testEndpoint('post', '/logout', $logoutToken);

        $this->info("\nDone!");
    }
}
