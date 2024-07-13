<?php
namespace App\Http\Livewire;

use App\Models\Attribution;
use App\Models\Classe;
use App\Models\Payment;
use App\Models\SchoolFees;
use App\Models\SchoolYear;
use App\Models\Student;
use Exception;
use Livewire\Component;

class CreatePayments extends Component
{
    public $matricule;
    public $montant;
    public $fullname;
    public $student_id;
    public $success;
    public $haveSuccess = false;
    public $error;
    public $haveError = false;
    protected $student;

    public function mount()
    {
        $this->validate([
            'matricule' => 'string|required',
            'montant' => 'numeric|required',
        ]);
    }

    public function store(Payment $payment)
    {
        $this->validate([
            'matricule' => 'string|required',
            'montant' => 'numeric|required',
        ]);

        if (!$this->student_id) {
            $this->error = 'Aucun étudiant trouvé avec ce matricule.';
            $this->haveError = true;
            $this->haveSuccess = false;
            return;
        }

        $activeSchoolYear = SchoolYear::where('active', '1')->first();
        $attribution = Attribution::where('student_id', $this->student_id)
            ->where('school_year_id', $activeSchoolYear->id)
            ->first();

        if (!$attribution) {
            $this->error = 'Aucune attribution trouvée pour cet étudiant.';
            $this->haveError = true;
            $this->haveSuccess = false;
            return;
        }

        $studentLevel = $attribution->classe->level;
        $schoolFees = SchoolFees::where('level_id', $studentLevel->id)
            ->where('school_year_id', $activeSchoolYear->id)
            ->first();

        $totalPaid = $this->student->payments()
            ->where('school_year_id', $activeSchoolYear->id)
            ->sum('montant');

        if (($totalPaid + $this->montant) > $schoolFees->montant) {
            $this->error = 'Attention. Il reste à payer ' . ($schoolFees->montant - $totalPaid) . ' Euro/Dollar';
            $this->haveError = true;
            $this->haveSuccess = false;
            return;
        }

        try {
            $payment->student_id = $this->student_id;
            $payment->classe_id = $attribution->classe_id;
            $payment->school_year_id = $activeSchoolYear->id;
            $payment->montant = $this->montant;
            $payment->save();
            $this->success = 'Paiement de scolarité effectué';
            $this->haveSuccess = true;
            $this->haveError = false;
        } catch (Exception $e) {
            $this->error = 'Une erreur s\'est produite lors de l\'enregistrement du paiement.';
            $this->haveError = true;
            $this->haveSuccess = false;
            report($e);
        }
    }

    public function render()
    {
        if (!$this->student) {
            $this->student = Student::where('matricule', $this->matricule)->first();
            if ($this->student) {
                $this->fullname = $this->student->nom . ' ' . $this->student->prenom;
                $this->student_id = $this->student->id;
            } else {
                $this->fullname = '';
                $this->student_id = null;
            }
        }
        return view('livewire.create-payments');
    }
}