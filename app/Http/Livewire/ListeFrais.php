<?php

namespace App\Http\Livewire;

use App\Models\SchoolFees;
use App\Models\SchoolYear;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ListeFrais extends Component
{
    use WithPagination;

    public $search = '';

    public function delete($id)
    {
        $fee = SchoolFees::find($id);
        if ($fee) {
            $fee->delete();
            return redirect()->route('niveaux')->with('success', 'Scolarité supprimé');
        } else {
            // Gérer le cas où l'objet $fee est null
            return redirect()->route('niveaux')->with('error', 'Impossible de supprimer la scolarité');
        }
    }

    public function render()
    {
        $activeSchoolYear = SchoolYear::where('active', '1')->first();

        if ($activeSchoolYear) {
            $fees = SchoolFees::with(['level', 'schoolyear'])
                ->whereRelation('schoolyear', 'school_year_id', $activeSchoolYear->id)
                ->paginate(10);
        } else {
            // Gérer le cas où $activeSchoolYear est null
            $fees = new LengthAwarePaginator([], 0, 10, 1);
        }

        return view('livewire.liste-frais', compact('fees'));
    }
}

?>