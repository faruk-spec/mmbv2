<?php
/**
 * CardX Project Configuration
 *
 * @package MMB\Projects\IDCard
 */

return [
    'name'        => 'CardX',
    'version'     => '1.0.0',
    'description' => 'AI-powered professional ID card generator with templates',

    'database' => [
        'host'     => 'localhost',
        'port'     => '3306',
        'database' => 'mmb_idcard',
        'username' => 'root',
        'password' => '',
    ],

    // Built-in template definitions
    'templates' => [
        'corporate' => [
            'name'        => 'Corporate',
            'description' => 'Clean corporate employee ID card',
            'color'       => '#1e40af',
            'accent'      => '#3b82f6',
            'bg'          => '#ffffff',
            'text'        => '#1e293b',
            'fields'      => ['name','designation','department','employee_id','phone','email','photo'],
            'logo'        => true,
        ],
        'student' => [
            'name'        => 'Student',
            'description' => 'University / school student ID card',
            'color'       => '#065f46',
            'accent'      => '#10b981',
            'bg'          => '#ffffff',
            'text'        => '#064e3b',
            'fields'      => ['name','course','roll_number','year','phone','email','photo'],
            'logo'        => true,
        ],
        'event' => [
            'name'        => 'Event',
            'description' => 'Conference / event attendee badge',
            'color'       => '#7c3aed',
            'accent'      => '#a78bfa',
            'bg'          => '#1e1b4b',
            'text'        => '#f5f3ff',
            'fields'      => ['name','title','organization','event_name','badge_id','photo'],
            'logo'        => true,
        ],
        'visitor' => [
            'name'        => 'Visitor',
            'description' => 'Visitor / guest pass',
            'color'       => '#b45309',
            'accent'      => '#f59e0b',
            'bg'          => '#fffbeb',
            'text'        => '#92400e',
            'fields'      => ['name','host_name','purpose','visit_date','badge_id','photo'],
            'logo'        => true,
        ],
        'medical' => [
            'name'        => 'Medical Staff',
            'description' => 'Hospital / clinic staff ID card',
            'color'       => '#0369a1',
            'accent'      => '#0ea5e9',
            'bg'          => '#f0f9ff',
            'text'        => '#0c4a6e',
            'fields'      => ['name','designation','department','license_no','phone','email','blood_group','photo'],
            'logo'        => true,
        ],
        'minimal' => [
            'name'        => 'Minimal Dark',
            'description' => 'Sleek dark-themed minimal ID card',
            'color'       => '#6366f1',
            'accent'      => '#818cf8',
            'bg'          => '#0f172a',
            'text'        => '#f1f5f9',
            'fields'      => ['name','title','organization','id_number','phone','photo'],
            'logo'        => false,
        ],
    ],

    // Field labels for the generator form
    'field_labels' => [
        'name'         => 'Full Name',
        'designation'  => 'Designation / Job Title',
        'department'   => 'Department',
        'employee_id'  => 'Employee ID',
        'roll_number'  => 'Roll / Enrollment No.',
        'course'       => 'Course / Program',
        'year'         => 'Year / Batch',
        'event_name'   => 'Event Name',
        'badge_id'     => 'Badge / Pass ID',
        'host_name'    => 'Host Name',
        'purpose'      => 'Purpose of Visit',
        'visit_date'   => 'Visit Date',
        'title'        => 'Title / Role',
        'organization' => 'Organization / Company',
        'id_number'    => 'ID Number',
        'license_no'   => 'License / Reg. No.',
        'blood_group'  => 'Blood Group',
        'phone'        => 'Phone / Mobile',
        'email'        => 'Email Address',
        'photo'        => 'Photo',
    ],
];
