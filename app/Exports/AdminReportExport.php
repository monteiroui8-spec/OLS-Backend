<?php

namespace App\Exports;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\TeacherProfile;
use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdminReportExport implements WithMultipleSheets
{
    public function __construct(private string $locale = 'pt')
    {
    }

    public function sheets(): array
    {
        return [
            'Resumo'         => new Sheets\SummarySheet($this->locale),
            'Alunos'         => new Sheets\StudentsSheet($this->locale),
            'Turmas'         => new Sheets\ClassesSheet($this->locale),
            'Notas'          => new Sheets\GradesSheet($this->locale),
            'Pagamentos'     => new Sheets\PaymentsSheet($this->locale),
            'Presencas'      => new Sheets\AttendanceSheet($this->locale),
        ];
    }
}
