<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmitted extends Notification
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

        return (new MailMessage)
            ->subject('تم استلام طلبك على وظيفة '.$title)
            ->greeting('مرحبًا '.$notifiable->name.'،')
            ->line('تم استلام طلبك على وظيفة "'.$title.'" بنجاح.')
            ->action('عرض الطلب', route('applications.show', $this->application))
            ->line('سنعلمك عند تحديث حالة طلبك.');
    }

    /**
     * @param  User  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم استلام طلبك',
            'body' => 'تم إرسال طلبك على وظيفة "'.$this->jobTitle().'".',
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
