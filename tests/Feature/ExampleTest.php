<?php

test('the application returns a successful response', function () {
    // ✅ FIX: استخدمنا الـ HealthController endpoint
    $response = $this->get('/api/health');

    $response->assertStatus(200);
});
