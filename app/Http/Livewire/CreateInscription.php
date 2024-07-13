<?php

namespace App\Http\Livewire;

use App\Models\Attribution;
use App\Models\Classe;
use App\Models\Level;
use App\Models\SchoolYear;
use App\Models\Student;
use Exception;
use Livewire\Component;

class CreateInscription extends Component
{
    public $student_id;
    public $level_id;
    public $matricule;
    public $classe_id;
    public $fullname;
    public $error;

    protected $rules = [
        'matricule' => 'string|required',
        'level_id' => 'integer|required',
        'classe_id' => 'integer|required',
    ];

    public function store()
    {
        $this->validate();

        $activeSchoolYear = SchoolYear::where('active', '1')->first();

        // Vérifier si l'année scolaire active existe
        if (!$activeSchoolYear) {
            $this->error = "Aucune année scolaire active n'a été trouvée.";
            return;
        }

        // Vérifier si l'élève est déjà inscrit pour l'année scolaire en cours
        if ($this->isStudentAlreadyEnrolled($this->student_id, $activeSchoolYear->id)) {
            $this->error = 'Cet élève est déjà inscrit. Modifier les informations si nécessaire.';
            return;
        }

        try {
            $this->createAttribution($this->student_id, $this->classe_id, $activeSchoolYear->id);
            return redirect()->route('inscriptions')->with('success', 'Inscription effectuée');
        } catch (Exception $e) {
            $this->error = 'Une erreur est survenue lors de l\'inscription.';
        }
    }

    protected function isStudentAlreadyEnrolled($studentId, $schoolYearId)
    {
        $student = Student::find($studentId);
        if ($student) {
            return Attribution::where('student_id', $student->id)
                ->where('school_year_id', $schoolYearId)
                ->exists();
        }
        return false;
    }

    protected function createAttribution($studentId, $classeId, $schoolYearId)
    {
        $attribution = new Attribution();
        $attribution->student_id = $studentId;
        $attribution->classe_id = $classeId;
        $attribution->school_year_id = $schoolYearId;
        $attribution->save();
    }

    public function render()
    {
        $activeSchoolYear = SchoolYear::where('active', '1')->first();

        // Vérifier si l'année scolaire active existe
        if ($activeSchoolYear) {
            $currentLevels = Level::where('school_year_id', $activeSchoolYear->id)->get();
        } else {
            $currentLevels = [];
        }

        $currentStudent = $this->student_id ? Student::find($this->student_id) : null;
        $this->fullname = $currentStudent ? $currentStudent->nom . ' ' . $currentStudent->prenom : '';

        $classeList = $this->level_id
            ? Classe::where('level_id', $this->level_id)->get()
            : [];

        return view('livewire.create-inscription', compact('currentLevels', 'classeList', 'currentStudent'));
    }
}