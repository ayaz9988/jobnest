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
}
