<?php

namespace App\Rules;

use App\Services\iDiarioService;
use Dotenv\Exception\ValidationException;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckGradesAndAbsencesInStageExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $turmaId = $value['schoolClass']->cod_turma;
        $anoTurma = $value['schoolClass']->ano;
        $etapasCount = count($value['startDates']);
        $etapasCountAntigo = $value['schoolClass']->stages()->count();

        if ($etapasCount < $etapasCountAntigo) {
            $etapasTmp = $etapasCount;
            $etapas = [];

            while ($etapasTmp < $etapasCountAntigo) {
                $etapasTmp += 1;
                $etapas[] = $etapasTmp;
            }

            $counts = [];

            $counts[] = DB::table('modules.falta_componente_curricular as fcc')
                ->join('modules.falta_aluno as fa', 'fa.id', '=', 'fcc.falta_aluno_id')
                ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'fa.matricula_id')
                ->join('pmieducar.matricula_turma as mt', 'mt.ref_cod_matricula', '=', 'm.cod_matricula')
                ->whereIn('fcc.etapa', $etapas)
                ->where('mt.ref_cod_turma', $turmaId)
                ->where('m.ativo', 1)
                ->count();

            $counts[] = DB::table('modules.falta_geral as fg')
                ->join('modules.falta_aluno as fa', 'fa.id', '=', 'fg.falta_aluno_id')
                ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'fa.matricula_id')
                ->join('pmieducar.matricula_turma as mt', 'mt.ref_cod_matricula', '=', 'm.cod_matricula')
                ->whereIn('fg.etapa', $etapas)
                ->where('mt.ref_cod_turma', $turmaId)
                ->where('m.ativo', 1)
                ->count();

            $counts[] = DB::table('modules.nota_componente_curricular as ncc')
                ->join('modules.nota_aluno as na', 'na.id', '=', 'ncc.nota_aluno_id')
                ->join('pmieducar.matricula as m', 'm.cod_matricula', '=', 'na.matricula_id')
                ->join('pmieducar.matricula_turma as mt', 'mt.ref_cod_matricula', '=', 'm.cod_matricula')
                ->whereIn('ncc.etapa', $etapas)
                ->where('mt.ref_cod_turma', $turmaId)
                ->where('m.ativo', 1)
                ->count();

            $sum = array_sum($counts);

            if ($sum > 0) {
                return false;
            }

            // Caso n??o exista token e URL de integra????o com o i-Di??rio, n??o ir??
            // validar se h?? lan??amentos nas etapas removidas

            $checkReleases = config('legacy.config.url_novo_educacao')
                && config('legacy.config.token_novo_educacao');

            if (!$checkReleases) {
                return true;
            }

            $iDiarioService = app(iDiarioService::class);

            foreach ($etapas as $etapa) {
                if ($iDiarioService->getStepActivityByClassroom($turmaId, $anoTurma, $etapa)) {
                    throw new ValidationException('N??o foi poss??vel remover uma das etapas pois existem notas ou faltas lan??adas no di??rio online.');
                }
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'N??o foi poss??vel remover uma das etapas pois existem notas ou faltas lan??adas.';
    }
}
