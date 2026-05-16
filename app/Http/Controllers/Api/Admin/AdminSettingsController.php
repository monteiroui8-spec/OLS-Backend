<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'institution_name' => 'nullable|string',
            'institution_code' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
            'timezone' => 'nullable|string',
            'default_language' => 'nullable|string',
            'currency' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'accent_color' => 'nullable|string',
            'enable_two_factor' => 'nullable|boolean',
            'require_strong_passwords' => 'nullable|boolean',
            'allow_public_registration' => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',
            'payment_reminders' => 'nullable|boolean',
            'exam_reminders' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, (string)$value);
        }

        return response()->json(['message' => 'Settings updated successfully.']);
    }
}
