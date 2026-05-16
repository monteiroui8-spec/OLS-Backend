<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Course extends Model {
    use HasUlids, SoftDeletes;
    public const SERVICE_TYPES = [
        'group_daily_communication',
        'individual',
        'kanuca',
        'oil_gas',
        'banking_finance',
        'corporate',
    ];

    protected $fillable = [
        'slug',
        'title_pt',
        'title_en',
        'description_pt',
        'description_en',
        'prerequisites',
        'level',
        'service_type',
        'duration',
        'start_date',
        'available_seats',
        'responsible_teacher_id',
        'syllabus',
        'required_material',
        'image_url',
        'flyer_url',
        'price_aoa',
        'price_eur',
        'price_usd',
        'is_active',
        'visibility_status',
        'published_at',
        'meta_description',
        'tags',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_aoa' => 'decimal:2',
        'price_eur' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'tags' => 'array',
        'start_date' => 'date',
        'published_at' => 'datetime',
        'available_seats' => 'integer',
    ];

    public function classes(): HasMany { return $this->hasMany(ClassGroup::class); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }
    public function documents(): HasMany { return $this->hasMany(Document::class); }
    public function responsibleTeacher(): BelongsTo { return $this->belongsTo(TeacherProfile::class, 'responsible_teacher_id'); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeVisibleOnSite($query)
    {
        return $query
            ->whereIn('visibility_status', ['published', 'scheduled'])
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function getTitle(string $lang = 'pt'): string
    {
        return $lang === 'en' ? $this->title_en : $this->title_pt;
    }

    public function getPriceForCurrency(string $currency): float
    {
        return match($currency) {
            'EUR'   => $this->price_eur,
            'USD'   => $this->price_usd,
            default => $this->price_aoa,
        };
    }

    public function getImageUrlAttribute($value): ?string
    {
        $filename = $this->courseImageFilename();
        if (!$filename) {
            return null;
        }

        $path = "courses/images/{$filename}";
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function getFlyerUrlAttribute($value): ?string
    {
        $filename = $this->flyerImageFilename();
        if (!$filename) {
            return null;
        }

        $path = "courses/images/{$filename}";
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function levelBucket(): string
    {
        $level = is_string($this->level) ? strtoupper(trim($this->level)) : '';

        return match (true) {
            in_array($level, ['BEGINNER', 'A1', 'A2'], true) => 'beginner',
            in_array($level, ['INTERMEDIATE', 'B1', 'B2'], true) => 'intermediate',
            in_array($level, ['ADVANCED', 'C1', 'C2'], true) => 'advanced',
            in_array($level, ['ALL'], true) => 'all',
            default => 'other',
        };
    }

    public function serviceCardMeta(string $lang = 'pt'): array
    {
        $isEn = strtolower($lang) === 'en';

        return match ((string) $this->service_type) {
            'group_daily_communication' => [
                'tag' => $isEn ? 'Most Popular' : 'Mais Popular',
                'tag_color' => 'bg-accent text-accent-foreground',
                'subtitle' => 'Daily Communication',
                'level_label' => 'A1 · A2 · B1 · B2',
                'duration_label' => $isEn ? 'Monthly' : 'Mensal',
                'instructor_label' => $isEn ? 'Certified teachers' : 'Professores certificados',
            ],
            'individual' => [
                'tag' => $isEn ? 'Personalised' : 'Personalizado',
                'tag_color' => 'bg-primary text-primary-foreground',
                'subtitle' => '1-on-1 Classes',
                'level_label' => $isEn ? 'All levels' : 'Todos os níveis',
                'duration_label' => $isEn ? 'Monthly' : 'Mensal',
                'instructor_label' => $isEn ? 'Dedicated teacher' : 'Professor dedicado',
            ],
            'kanuca' => [
                'tag' => $isEn ? 'Children' : 'Crianças',
                'tag_color' => 'bg-green-600 text-white',
                'subtitle' => 'Junior English Program',
                'level_label' => $isEn ? 'Ages 8–12' : '8–12 anos',
                'duration_label' => $isEn ? 'Monthly' : 'Mensal',
                'instructor_label' => $isEn ? 'Children specialists' : 'Especialistas em crianças',
            ],
            'oil_gas' => [
                'tag' => 'Premium',
                'tag_color' => 'bg-purple-600 text-white',
                'subtitle' => 'Professional English Track',
                'level_label' => $isEn ? 'Intermediate–Advanced' : 'Intermédio–Avançado',
                'duration_label' => $isEn ? 'Monthly' : 'Mensal',
                'instructor_label' => $isEn ? 'Industry specialists' : 'Especialistas da indústria',
            ],
            'banking_finance' => [
                'tag' => 'Premium',
                'tag_color' => 'bg-purple-600 text-white',
                'subtitle' => 'Professional English Track',
                'level_label' => $isEn ? 'Intermediate–Advanced' : 'Intermédio–Avançado',
                'duration_label' => $isEn ? 'Monthly' : 'Mensal',
                'instructor_label' => $isEn ? 'Finance specialists' : 'Especialistas financeiros',
            ],
            'corporate' => [
                'tag' => $isEn ? 'Corporate' : 'Empresas',
                'tag_color' => 'bg-foreground text-background',
                'subtitle' => $isEn ? 'For Companies & Institutions' : 'Para Empresas & Instituições',
                'level_label' => $isEn ? 'All levels' : 'Todos os níveis',
                'duration_label' => $isEn ? 'Contract' : 'Contrato',
                'instructor_label' => $isEn ? 'Dedicated team' : 'Equipa dedicada',
            ],
            default => [
                'tag' => $isEn ? 'Course' : 'Curso',
                'tag_color' => 'bg-muted text-muted-foreground',
                'subtitle' => '',
                'level_label' => '',
                'duration_label' => '',
                'instructor_label' => '',
            ],
        };
    }

    private function flyerImageFilename(): ?string
    {
        return match ((string) $this->service_type) {
            'group_daily_communication' => 'flyer-group.png',
            'individual' => 'flyer-individual.png',
            'kanuca' => 'flyer-kanuca.png',
            'oil_gas' => 'flyer-oilgas.png',
            'banking_finance' => 'flyer-banking.png',
            'corporate' => 'flyer-corporate.png',
            default => null,
        };
    }

    private function courseImageFilename(): ?string
    {
        $serviceType = (string) $this->service_type;

        $byService = match ($serviceType) {
            'banking_finance', 'oil_gas', 'corporate' => 'course-business.jpg',
            'group_daily_communication' => 'course-conversation.jpg',
            'kanuca' => 'course-beginner.jpg',
            default => null,
        };

        if ($byService) {
            return $byService;
        }

        $bucket = $this->levelBucket();
        return match ($bucket) {
            'beginner' => 'course-beginner.jpg',
            'intermediate' => 'course-intermediate.jpg',
            'advanced' => 'course-advanced.jpg',
            default => 'course-business.jpg',
        };
    }
}
