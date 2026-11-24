<?php

return [
    'resources' => [
        'admin' => [
            'singular' => 'Admin',
            'plural' => 'Admins',
            'navigation' => 'Admins',
        ],
        'application' => [
            'singular' => 'Application',
            'plural' => 'Applications',
            'navigation' => 'Applications',
        ],
        'category' => [
            'singular' => 'Category',
            'plural' => 'Categories',
            'navigation' => 'Categories',
        ],
        'job_listing' => [
            'singular' => 'Job listing',
            'plural' => 'Job listings',
            'navigation' => 'Job listings',
        ],
        'resume' => [
            'singular' => 'Resume',
            'plural' => 'Resumes',
            'navigation' => 'Resumes',
        ],
        'skill' => [
            'singular' => 'Skill',
            'plural' => 'Skills',
            'navigation' => 'Skills',
        ],
        'user' => [
            'singular' => 'User',
            'plural' => 'Users',
            'navigation' => 'Users',
        ],
    ],

    'fields' => [
        'name' => 'Name',
        'email' => 'Email address',
        'email_verified_at' => 'Email verified at',
        'password' => 'Password',
        'role' => 'Role',
        'is_active' => 'Active',
        'last_login_at' => 'Last login at',
        'last_login_ip' => 'Last login IP',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
        'applicant' => 'Applicant',
        'user' => 'User',
        'job_listing' => 'Job listing',
        'resume' => 'Resume',
        'status' => 'Status',
        'cover_letter' => 'Cover letter',
        'applied_at' => 'Applied at',
        'category' => 'Category',
        'employer' => 'Employer',
        'posted_by' => 'Posted by',
        'title' => 'Title',
        'description' => 'Description',
        'employment_type' => 'Employment type',
        'workplace_type' => 'Workplace type',
        'location' => 'Location',
        'salary_min' => 'Minimum salary',
        'salary_max' => 'Maximum salary',
        'salary_currency' => 'Salary currency',
        'expires_at' => 'Expires at',
        'published_at' => 'Published at',
        'deleted_at' => 'Deleted at',
        'resume_file' => 'Resume file',
        'file_name' => 'File name',
        'mime_type' => 'MIME type',
        'file_size' => 'Size',
        'is_primary' => 'Primary resume',
        'profile' => 'Profile',
        'two_factor_confirmed_at' => 'Two-factor confirmed at',
        'parent_category' => 'Parent category',
        'slug' => 'Slug',
    ],

    'placeholders' => [
        'none_top_level' => 'None (top-level)',
        'dash' => '—',
    ],

    'helper_text' => [
        'choose_applicant_first' => 'Choose an applicant first.',
        'resume_file' => 'PDF, DOC or DOCX up to 5 MB.',
    ],

    'options' => [
        'admin_roles' => [
            'admin' => 'Admin',
            'super_admin' => 'Super admin',
        ],
        'user_roles' => [
            'seeker' => 'Seeker',
            'employer' => 'Employer',
        ],
        'application_statuses' => [
            'applied' => 'Applied',
            'reviewed' => 'Reviewed',
            'interview' => 'Interview',
            'withdraw' => 'Withdrawn',
            'hired' => 'Hired',
            'rejected' => 'Rejected',
        ],
        'employment_types' => [
            'full_time' => 'Full time',
            'part_time' => 'Part time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'temporary' => 'Temporary',
        ],
        'workplace_types' => [
            'on_site' => 'On site',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
        ],
        'job_statuses' => [
            'draft' => 'Draft',
            'open' => 'Open',
            'closed' => 'Closed',
            'filled' => 'Filled',
        ],
    ],

    'filters' => [
        'active_admins' => 'Active admins',
    ],

    'widgets' => [
        'applications_chart' => [
            'heading' => 'Applications over time',
            'description' => 'Last 30 days',
            'dataset' => 'Applications',
        ],
        'jobs_by_status' => [
            'heading' => 'Job listings by status',
            'description' => 'All listings',
            'dataset' => 'Listings',
        ],
        'latest_applications' => [
            'heading' => 'Latest applications',
        ],
        'latest_job_listings' => [
            'heading' => 'Latest job listings',
        ],
        'stats' => [
            'users' => [
                'label' => 'Users',
                'description' => 'All registered accounts',
            ],
            'seekers' => [
                'label' => 'Seekers',
                'description' => 'Job seekers',
            ],
            'employers' => [
                'label' => 'Employers',
                'description' => 'Companies posting jobs',
            ],
            'open_jobs' => [
                'label' => 'Open jobs',
                'description' => 'Visible to seekers',
            ],
            'applications' => [
                'label' => 'Applications',
                'description' => 'All statuses',
            ],
        ],
    ],

    'actions' => [
        'mark_reviewed' => 'Mark reviewed',
        'schedule_interview' => 'Schedule interview',
        'mark_hired' => 'Mark hired',
        'reject' => 'Reject',
        'download' => 'Download',
    ],

    'notifications' => [
        'application_status_updated' => 'Application status updated',
        'application_not_updated' => 'Application not updated',
    ],

    'errors' => [
        'invalid_application_transition' => 'Cannot move this application from :from to :to.',
        'cannot_delete_self' => 'You cannot delete yourself.',
        'last_super_admin_delete' => 'The last active super admin cannot be deleted.',
        'last_super_admin_demote' => 'The last active super admin cannot be demoted or deactivated.',
        'cannot_change_own_role' => 'You cannot change your own role.',
        'cannot_deactivate_self' => 'You cannot deactivate your own account.',
        'category_parent_self' => 'A category cannot be its own parent or one of its descendants.',
    ],
];
