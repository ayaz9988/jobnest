<?php

namespace App\Support;

final class JobListingOptions
{
    /**
     * @return array<string, string>
     */
    public static function employmentTypes(): array
    {
        return [
            'full_time' => 'دوام كامل',
            'part_time' => 'دوام جزئي',
            'contract' => 'عقد',
            'internship' => 'تدريب',
            'temporary' => 'مؤقت',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function workplaceTypes(): array
    {
        return [
            'remote' => 'عن بُعد',
            'on_site' => 'في الموقع',
            'hybrid' => 'هجين',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function applicationStatuses(): array
    {
        return [
            'applied' => 'تم التقديم',
            'reviewed' => 'قيد المراجعة',
            'interview' => 'مقابلة',
            'withdraw' => 'منسحب',
            'hired' => 'تم التوظيف',
            'rejected' => 'مرفوض',
        ];
    }

    public static function employmentType(?string $type): string
    {
        return self::employmentTypes()[$type] ?? 'غير محدد';
    }

    public static function workplaceType(?string $type): string
    {
        return self::workplaceTypes()[$type] ?? 'غير محدد';
    }

    public static function applicationStatus(?string $status): string
    {
        return self::applicationStatuses()[$status] ?? 'غير محدد';
    }

    public static function applicationStatusColor(?string $status): string
    {
        return match ($status) {
            'applied' => 'blue',
            'reviewed' => 'amber',
            'interview' => 'blue',
            'hired' => 'green',
            'rejected' => 'red',
            default => 'zinc',
        };
    }

    public static function applicationStatusDot(?string $status): string
    {
        return match ($status) {
            'applied' => 'bg-blue-500',
            'reviewed' => 'bg-amber-500',
            'interview' => 'bg-sky-500',
            'hired' => 'bg-green-500',
            'rejected' => 'bg-red-500',
            default => 'bg-zinc-400',
        };
    }
}
