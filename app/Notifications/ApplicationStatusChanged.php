<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\User;
use App\Support\JobListingOptions;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification
{
    public function __construct(
        private readonly Application $application,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @param  User  $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->jobTitle();
        $status = JobListingOptions::applicationStatus($this->application->status);

        return (new MailMessage)
            ->subject('تحديث حالة طلبك')
            ->greeting('مرحبًا '.$notifiable->name.'،')
            ->line('تم تحديث حالة طلبك على وظيفة "'.$title.'" إلى: '.$status.'.')
            ->action('عرض الطلب', route('applications.show', $this->application));
    }

    /**
     * @param  User  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $status = JobListingOptions::applicationStatus($this->application->status);

        return [
            'title' => 'تحديث حالة طلبك',
            'body' => 'أصبحت حالة طلبك على وظيفة "'.$this->jobTitle().'" '.$status.'.',
            'url' => route('applications.show', $this->application),
            'application_id' => $this->application->getKey(),
            'status' => $this->application->status,
        ];
    }

    private function jobTitle(): string
    {
        return $this->application->jobListing->title ?? 'وظيفة محذوفة';
    }
}
