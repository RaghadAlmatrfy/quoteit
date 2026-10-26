<?php

/*
 * Arabic validation messages (only the rules used in this project).
 */
return [
    'after' => 'يجب أن يكون :attribute بعد :date.',
    'date' => 'حقل :attribute يجب أن يكون تاريخًا صحيحًا.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'required_if' => 'حقل :attribute مطلوب.',
    'alpha_dash' => 'حقل :attribute يقبل الحروف الإنجليزية والأرقام و - و _ فقط.',
    'array' => 'حقل :attribute غير صالح.',
    'boolean' => 'حقل :attribute غير صالح.',
    'email' => 'حقل :attribute يجب أن يكون بريدًا إلكترونيًا صحيحًا.',
    'enum' => 'القيمة المختارة في :attribute غير صالحة.',
    'file' => 'حقل :attribute يجب أن يكون ملفًا.',
    'image' => 'حقل :attribute يجب أن يكون صورة.',
    'in' => 'القيمة المختارة في :attribute غير صالحة.',
    'max' => [
        'file' => 'حجم :attribute يجب ألا يزيد عن :max كيلوبايت.',
        'string' => 'حقل :attribute يجب ألا يزيد عن :max حرفًا.',
        'array' => 'حقل :attribute يجب ألا يحتوي أكثر من :max عناصر.',
    ],
    'mimes' => 'حقل :attribute يجب أن يكون من نوع: :values.',
    'min' => [
        'file' => 'حجم :attribute يجب أن يكون :min كيلوبايت على الأقل.',
        'string' => 'حقل :attribute يجب أن يكون :min أحرف على الأقل.',
    ],
    'required' => 'حقل :attribute مطلوب.',
    'required_without' => 'اكتب :attribute أو أرفق ملفًا.',
    'unique' => 'هذا :attribute مستخدم من قبل.',
    'custom' => [
        'publish_at' => ['after' => 'اختر موعدًا في المستقبل.'],
    ],
    'uploaded' => 'فشل رفع :attribute.',

    'attributes' => [
        'name' => 'الاسم',
        'username' => 'اسم المستخدم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'content' => 'الاقتباس',
        'field' => 'التخصص',
        'media' => 'الصورة / الفيديو',
        'bio' => 'النبذة',
        'specialty' => 'التخصص',
        'interests' => 'الاهتمامات',
        'avatar' => 'الصورة الشخصية',
        'body' => 'النص',
        'publish_at' => 'موعد النشر',
        'bg_color' => 'لون الخلفية',
        'font' => 'الخط',
        'font_size' => 'حجم الخط',
    ],
];
