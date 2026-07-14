<?php

return [
    'only_draft_invoices_can_be_updated' => 'Only draft invoices can be updated.',
    'only_draft_invoices_can_be_deleted' => 'Only draft invoices can be deleted.',
    'xml_generated' => 'Invoice XML and QR code generated successfully.',
    'xml_signed' => 'Invoice signed successfully.',
    'xml_queued' => 'XML generation has been queued.',
    'generate_xml_first' => 'You must generate the XML first.',
    'sign_queued' => 'Invoice signing has been queued.',
    'submission_queued' => 'Invoice submission to ZATCA has been queued.',
    'processing_queued' => 'Invoice processing (generate → sign → submit) has been queued.',
    'saleh7_zatca_package_not_installed' => 'The saleh7/php-zatca-xml package is not installed. Run: composer require saleh7/php-zatca-xml',
    'company_certificate_not_ready' => 'This company has not completed ZATCA onboarding yet (no certificate found).',
    'invoice_not_signed_yet' => 'This invoice must be signed before submission. Call sign-xml first.',
    'submitted_to_zatca' => 'Invoice submitted to ZATCA successfully.',
];
