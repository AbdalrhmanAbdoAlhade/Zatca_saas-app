<?php

return [
    'only_draft_invoices_can_be_updated' => 'يمكن تعديل الفواتير في حالة المسودة فقط.',
    'only_draft_invoices_can_be_deleted' => 'يمكن حذف الفواتير في حالة المسودة فقط.',
    'xml_generated' => 'تم توليد XML وكود QR للفاتورة بنجاح.',
    'xml_signed' => 'تم توقيع الفاتورة رقمياً بنجاح.',
    'xml_queued' => 'تم جدولة توليد XML.',
    'generate_xml_first' => 'لازم تولّد الـ XML الأول.',
    'sign_queued' => 'تم جدولة توقيع الفاتورة.',
    'submission_queued' => 'تم جدولة إرسال الفاتورة لـ ZATCA.',
    'processing_queued' => 'تم جدولة معالجة الفاتورة كاملة (توليد ← توقيع ← إرسال).',
    'saleh7_zatca_package_not_installed' => 'مكتبة saleh7/php-zatca-xml مش متثبتة. شغّل: composer require saleh7/php-zatca-xml',
    'company_certificate_not_ready' => 'الشركة لسه معملتش onboarding مع ZATCA (مفيش شهادة).',
    'invoice_not_signed_yet' => 'لازم توقّع الفاتورة الأول قبل الإرسال. نادي sign-xml قبلها.',
    'submitted_to_zatca' => 'تم إرسال الفاتورة لـ ZATCA بنجاح.',
];
