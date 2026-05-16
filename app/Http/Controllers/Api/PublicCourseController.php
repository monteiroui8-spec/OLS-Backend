<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\StudentProfile;
use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PublicCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'pt');

        $courses = Cache::remember(
            'public_courses_'.$lang,
            now()->addMinutes(30),
            function () use ($lang) {
                return Course::active()
                    ->visibleOnSite()
                    ->orderBy('service_type')
                    ->get()
                    ->map(function (Course $course) use ($lang) {
                        $title = $lang === 'en' ? $course->title_en : $course->title_pt;
                        $description = $lang === 'en' ? $course->description_en : $course->description_pt;
                        $card = $course->serviceCardMeta($lang);

                        return [
                            'id' => $course->id,
                            'slug' => $course->slug,
                            'title' => $title,
                            'description' => $description,
                            'level' => $course->levelBucket(),
                            'levelRaw' => $course->level,
                            'serviceType' => $course->service_type,
                            'flyerUrl' => $course->flyer_url,
                            'imageUrl' => $course->image_url,
                            'duration' => $course->duration,
                            'startDate' => $course->start_date?->toDateString(),
                            'availableSeats' => $course->available_seats,
                            'prerequisites' => $course->prerequisites,
                            'syllabus' => $course->syllabus,
                            'requiredMaterial' => $course->required_material,
                            'tags' => $course->tags ?? [],
                            'card' => [
                                'tag' => $card['tag'] ?? null,
                                'tagColor' => $card['tag_color'] ?? null,
                                'subtitle' => $card['subtitle'] ?? null,
                                'levelLabel' => $card['level_label'] ?? null,
                                'durationLabel' => $card['duration_label'] ?? null,
                                'instructorLabel' => $card['instructor_label'] ?? null,
                            ],
                            'prices' => [
                                'AOA' => $course->price_aoa,
                                'EUR' => $course->price_eur,
                                'USD' => $course->price_usd,
                            ],
                        ];
                    });
            }
        );

        return response()->json([
            'data' => $courses,
        ]);
    }

    public function classes(Course $course): JsonResponse
    {
        $classes = ClassGroup::with('schedules')
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->orderByDesc('year')
            ->orderBy('name')
            ->get();

        $data = $classes->map(function (ClassGroup $class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'year' => $class->year,
                'capacity' => $class->capacity,
                'room' => $class->room,
                'schedules' => $class->schedules->map(function (ClassSchedule $schedule) {
                    return [
                        'id' => $schedule->id,
                        'day_of_week' => $schedule->day_of_week,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = Cache::remember(
            'public_stats',
            now()->addMinutes(60),
            function () {
                return [
                    'active_students' => StudentProfile::count(),
                    'total_courses' => Course::active()->count(),
                    'certified_teachers' => User::where('role', 'teacher')->count(),
                    'success_rate' => 98,
                ];
            }
        );

        return response()->json($stats);
    }

    public function testimonials(): JsonResponse
    {
        $testimonials = \App\Models\Testimonial::where('is_active', true)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return response()->json([
            'data' => $testimonials,
        ]);
    }
}
