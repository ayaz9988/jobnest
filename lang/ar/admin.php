<?php

return [
    'resources' => [
        'admin' => [
            'singular' => 'مشرف',
            'plural' => 'المشرفون',
            'navigation' => 'المشرفون',
        ],
        'application' => [
            'singular' => 'طلب توظيف',
            'plural' => 'طلبات التوظيف',
            'navigation' => 'طلبات التوظيف',
        ],
        'category' => [
            'singular' => 'فئة',
            'plural' => 'الفئات',
            'navigation' => 'الفئات',
        ],
        'job_listing' => [
            'singular' => 'إعلان وظيفي',
            'plural' => 'إعلانات الوظائف',
            'navigation' => 'إعلانات الوظائف',
        ],
        'resume' => [
            'singular' => 'سيرة ذاتية',
            'plural' => 'السير الذاتية',
            'navigation' => 'السير الذاتية',
        ],
        'skill' => [
            'singular' => 'مهارة',
            'plural' => 'المهارات',
            'navigation' => 'المهارات',
        ],
        'user' => [
            'singular' => 'مستخدم',
            'plural' => 'المستخدمون',
            'navigation' => 'المستخدمون',
        ],
    ],

    'fields' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'email_verified_at' => 'تاريخ التحقق من البريد',
        'password' => 'كلمة المرور',
        'role' => 'الدور',
        'is_active' => 'نشط',
        'last_login_at' => 'آخر تسجيل دخول',
        'last_login_ip' => 'آخر عنوان IP',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'applicant' => 'المتقدم',
        'user' => 'المستخدم',
        'job_listing' => 'إعلان الوظيفة',
        'resume' => 'السيرة الذاتية',
        'status' => 'الحالة',
        'cover_letter' => 'خطاب التقديم',
        'applied_at' => 'تاريخ التقديم',
        'category' => 'الفئة',
        'employer' => 'صاحب العمل',
        'posted_by' => 'الناشر',
        'title' => 'العنوان',
        'description' => 'الوصف',
        'employment_type' => 'نوع الدوام',
        'workplace_type' => 'مكان العمل',
        'location' => 'الموقع',
        'salary_min' => 'الحد الأدنى للراتب',
        'salary_max' => 'الحد الأعلى للراتب',
        'salary_currency' => 'عملة الراتب',
        'expires_at' => 'تاريخ الانتهاء',
        'published_at' => 'تاريخ النشر',
        'deleted_at' => 'تاريخ الحذف',
        'resume_file' => 'ملف السيرة الذاتية',
        'file_name' => 'اسم الملف',
        'mime_type' => 'نوع الملف',
        'file_size' => 'الحجم',
        'is_primary' => 'السيرة الذاتية الأساسية',
        'profile' => 'النبذة',
        'two_factor_confirmed_at' => 'تاريخ تأكيد التحقق بخطوتين',
        'parent_category' => 'الفئة الأصلية',
        'slug' => 'المعرف النصي',
    ],

    'placeholders' => [
        'none_top_level' => 'بدون (فئة رئيسية)',
        'dash' => '—',
    ],

    'helper_text' => [
        'choose_applicant_first' => 'اختر المتقدم أولًا.',
        'resume_file' => 'PDF أو DOC أو DOCX بحد أقصى 5 ميجابايت.',
    ],

    'options' => [
        'admin_roles' => [
            'admin' => 'مشرف',
            'super_admin' => 'مشرف عام',
        ],
        'user_roles' => [
            'seeker' => 'باحث عن عمل',
            'employer' => 'صاحب عمل',
        ],
        'application_statuses' => [
            'applied' => 'تم التقديم',
            'reviewed' => 'قيد المراجعة',
            'interview' => 'مقابلة',
            'withdraw' => 'منسحب',
            'hired' => 'تم التوظيف',
            'rejected' => 'مرفوض',
        ],
        'employment_types' => [
            'full_time' => 'دوام كامل',
            'part_time' => 'دوام جزئي',
            'contract' => 'عقد',
            'internship' => 'تدريب',
            'temporary' => 'مؤقت',
        ],
        'workplace_types' => [
            'on_site' => 'في الموقع',
            'remote' => 'عن بُعد',
            'hybrid' => 'هجين',
        ],
        'job_statuses' => [
            'draft' => 'مسودة',
            'open' => 'مفتوح',
            'closed' => 'مغلق',
            'filled' => 'مكتمل',
        ],
    ],

    'filters' => [
        'active_admins' => 'المشرفون النشطون',
    ],

    'widgets' => [
        'applications_chart' => [
            'heading' => 'الطلبات عبر الوقت',
            'description' => 'آخر 30 يومًا',
            'dataset' => 'الطلبات',
        ],
        'jobs_by_status' => [
            'heading' => 'إعلانات الوظائف حسب الحالة',
            'description' => 'جميع الإعلانات',
            'dataset' => 'الإعلانات',
        ],
        'latest_applications' => [
            'heading' => 'أحدث طلبات التوظيف',
        ],
        'latest_job_listings' => [
            'heading' => 'أحدث إعلانات الوظائف',
        ],
        'stats' => [
            'users' => [
                'label' => 'المستخدمون',
                'description' => 'جميع الحسابات المسجلة',
            ],
            'seekers' => [
                'label' => 'الباحثون عن عمل',
                'description' => 'حسابات الباحثين عن عمل',
            ],
            'employers' => [
                'label' => 'أصحاب العمل',
                'description' => 'الشركات التي تنشر الوظائف',
            ],
            'open_jobs' => [
                'label' => 'الوظائف المفتوحة',
                'description' => 'مرئية للباحثين عن عمل',
            ],
            'applications' => [
                'label' => 'الطلبات',
                'description' => 'جميع الحالات',
            ],
        ],
    ],

    'actions' => [
        'mark_reviewed' => 'تحديد كمُراجَع',
        'schedule_interview' => 'جدولة مقابلة',
        'mark_hired' => 'تحديد كموظف',
        'reject' => 'رفض',
        'download' => 'تنزيل',
    ],

    'notifications' => [
        'application_status_updated' => 'تم تحديث حالة الطلب',
        'application_not_updated' => 'لم يتم تحديث الطلب',
    ],

    'errors' => [
        'invalid_application_transition' => 'لا يمكن نقل هذا الطلب من :from إلى :to.',
        'cannot_delete_self' => 'لا يمكنك حذف نفسك.',
        'last_super_admin_delete' => 'لا يمكن حذف آخر مشرف عام نشط.',
        'last_super_admin_demote' => 'لا يمكن تخفيض دور آخر مشرف عام نشط أو تعطيله.',
        'cannot_change_own_role' => 'لا يمكنك تغيير دورك.',
        'cannot_deactivate_self' => 'لا يمكنك تعطيل حسابك.',
        'category_parent_self' => 'لا يمكن أن تكون الفئة أصلًا لنفسها أو لإحدى الفئات التابعة لها.',
    ],
];
