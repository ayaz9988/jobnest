<?php

namespace Database\Seeders;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ResumeSeeder extends Seeder
{
    public function run(): void
    {
        $resumes = [
            [
                'email' => 'seeker@example.com',
                'file_name' => 'ahmed-khalil-cv.pdf',
                'file_path' => 'resumes/demo/ahmed-khalil-cv.pdf',
                'is_primary' => true,
            ],
            [
                'email' => 'seeker@example.com',
                'file_name' => 'ahmed-khalil-portfolio.pdf',
                'file_path' => 'resumes/demo/ahmed-khalil-portfolio.pdf',
                'is_primary' => false,
            ],
            [
                'email' => 'sara@example.com',
                'file_name' => 'sara-ahmad-cv.pdf',
                'file_path' => 'resumes/demo/sara-ahmad-cv.pdf',
                'is_primary' => true,
            ],
            [
                'email' => 'omar@example.com',
                'file_name' => 'omar-khaled-cv.pdf',
                'file_path' => 'resumes/demo/omar-khaled-cv.pdf',
                'is_primary' => true,
            ],
            [
                'email' => 'test@example.com',
                'file_name' => 'test-user-cv.pdf',
                'file_path' => 'resumes/demo/test-user-cv.pdf',
                'is_primary' => true,
            ],
        ];

        $disk = Storage::disk('local');

        foreach ($resumes as $data) {
            $user = User::query()->where('email', $data['email'])->firstOrFail();

            if (! $disk->exists($data['file_path'])) {
                $disk->put($data['file_path'], $this->placeholderPdf($user->name));
            }

            Resume::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'file_name' => $data['file_name'],
                ],
                [
                    'file_path' => $data['file_path'],
                    'mime_type' => 'application/pdf',
                    'file_size' => $disk->size($data['file_path']),
                    'is_primary' => $data['is_primary'],
                ],
            );
        }
    }

    private function placeholderPdf(string $name): string
    {
        return "%PDF-1.4\n% Demo resume for {$name}\n%%EOF\n";
    }
}
