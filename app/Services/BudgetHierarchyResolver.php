<?php

namespace App\Services;

use App\Models\BudgetActivity;
use App\Models\BudgetAffair;
use App\Models\BudgetProgram;
use App\Models\BudgetSubactivity;

class BudgetHierarchyResolver
{
    private array $resolved = [];

    public function resolve(?array $hierarchy): ?int
    {
        if ($hierarchy === null) {
            return null;
        }
        $key = json_encode($hierarchy, JSON_THROW_ON_ERROR);
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }
        // The source template supplies names, not official hierarchy codes.
        // Each name is identified within its parent, never globally across parents.
        $affair = BudgetAffair::firstOrCreate(['name' => $hierarchy['NAMA_URUSAN']]);
        $program = BudgetProgram::firstOrCreate(['budget_affair_id' => $affair->id, 'name' => $hierarchy['NAMA_PROGRAM']]);
        $activity = BudgetActivity::firstOrCreate(['budget_program_id' => $program->id, 'name' => $hierarchy['NAMA_KEGIATAN']]);
        $subactivity = BudgetSubactivity::firstOrCreate(['budget_activity_id' => $activity->id, 'name' => $hierarchy['NAMA_SUB_KEGIATAN']]);

        return $this->resolved[$key] = $subactivity->id;
    }
}
