<?php

namespace iEducar\Modules\Educacenso\Data;

use App\Models\Educacenso\Registro00 as Registro00Model;
use iEducar\Modules\Educacenso\ExportRule\DependenciaAdministrativa;
use iEducar\Modules\Educacenso\ExportRule\EsferaAdministrativa;
use iEducar\Modules\Educacenso\ExportRule\PoderPublicoConveniado as ExportRulePoderPublicoConveniado;
use iEducar\Modules\Educacenso\ExportRule\Regulamentacao;
use iEducar\Modules\Educacenso\ExportRule\SituacaoFuncionamento;
use iEducar\Modules\Educacenso\Formatters;
use iEducar\Modules\Educacenso\Model\FormasContratacaoPoderPublico;
use iEducar\Modules\Educacenso\Model\PoderPublicoConveniado;
use Portabilis_Date_Utils;
use Portabilis_Utils_Database;

class Registro00 extends AbstractRegistro
{
    use Formatters;

    /**
     * @var Registro00Model
     */
    protected $model;

    public $codigoInep;
    public $nomeEscola;
    public $situacaoFuncionamento;

    /**
     * @param $escola
     * @param $ano
     *
     * @return Registro00Model
     */
    public function getData($school, $year)
    {
        $data = $this->repository->getDataForRecord00($school, $year);

        $models = [];
        foreach ($data as $record) {
            $record = $this->processData($record);
            $models[] = $this->hydrateModel($record);
        }

        return $models[0];
    }

    /**
     * @param $escola
     * @param $year
     *
     */
    public function getExportFormatData($escola, $year)
    {
        $data = $this->getData($escola, $year);
        $data = $this->getRecordExportData($data);

        return $data;
    }

    /**
     * @param $Registro00Model
     *
     * @return array
     */
    public function getRecordExportData($record)
    {
        $this->codigoInep = $record->codigoInep;
        $this->nomeEscola = $record->nome;
        $this->situacaoFuncionamento = $record->situacaoFuncionamento;

        $record = SituacaoFuncionamento::handle($record);
        $record = ExportRulePoderPublicoConveniado::handle($record);
        $record = DependenciaAdministrativa::handle($record);
        $record = Regulamentacao::handle($record);
        $record = EsferaAdministrativa::handle($record);

        return [
            $record->registro, // 1	Tipo de registro
            $record->codigoInep, // 2	C??digo de escola - Inep
            $record->situacaoFuncionamento, // 3	Situa????o de funcionamento
            $record->inicioAnoLetivo, // 4	Data de in??cio do ano letivo
            $record->fimAnoLetivo, // 5	Data de t??rmino do ano letivo
            $record->nome, // 6	Nome da escola
            $record->cep, // 7	CEP
            $record->codigoIbgeMunicipio, // 8	Munic??pio
            $record->codigoIbgeDistrito, // 9	Distrito
            $record->logradouro, // 10	Endere??o
            $record->numero, // 11	N??mero
            $record->complemento, // 12	Complemento
            $record->bairro, // 13	Bairro
            $record->ddd, // 14	DDD
            $record->telefone, // 15	Telefone
            $record->telefoneOutro, // 16	Outro telefone de contato
            $record->email, // 17	Endere??o eletr??nico (e-mail) da escola
            $record->orgaoRegional, // 18	C??digo do ??rg??o regional de ensino
            $record->zonaLocalizacao, // 19	Localiza????o/Zona da escola
            $record->localizacaoDiferenciada, // 20	Localiza????o diferenciada da escola
            $record->dependenciaAdministrativa, // 21	Depend??ncia administrativa
            $record->orgaoEducacao, // 22	Secretaria de Educa????o/Minist??rio da Educa????o
            $record->orgaoSeguranca, // 23	Secretaria de Seguran??a P??blica/For??as Armadas/Militar
            $record->orgaoSaude, // 24	Secretaria da Sa??de/Minist??rio da Sa??de
            $record->orgaoOutro, // 25	Outro ??rg??o da administra????o p??blica
            $record->mantenedoraEmpresa, // 26	Empresa, grupos empresariais do setor privado ou pessoa f??sica
            $record->mantenedoraSindicato, // 27	Sindicatos de trabalhadores ou patronais, associa????es, cooperativas
            $record->mantenedoraOng, // 28	Organiza????o n??o governamental (ONG) - nacional ou internacional
            $record->mantenedoraInstituicoes, // 29	Institui????o sem fins lucrativos
            $record->mantenedoraSistemaS, // 30	Sistema S (Sesi, Senai, Sesc, outros)
            $record->mantenedoraOscip, // 31	Organiza????o da Sociedade Civil de Interesse P??blico (Oscip)
            $record->categoriaEscolaPrivada, // 32	Categoria da escola privada
            $record->poderPublicoConveniado ? (int) in_array(PoderPublicoConveniado::ESTADUAL, $record->poderPublicoConveniado) : '', // 33	Secretaria estadual
            $record->poderPublicoConveniado ? (int) in_array(PoderPublicoConveniado::MUNICIPAL, $record->poderPublicoConveniado) : '', // 34	Secretaria Municipal
            $record->poderPublicoConveniado ? (int) in_array(PoderPublicoConveniado::NAO_POSSUI, $record->poderPublicoConveniado) : '', // 35	N??o possui parceria ou conv??nio
            $record->formasContratacaoPoderPublico ? (int) in_array(FormasContratacaoPoderPublico::TERMO_COLABORACAO, $record->formasContratacaoPoderPublico) : '', // 36	Termo de colabora????o (Lei n?? 13.019/2014)
            $record->formasContratacaoPoderPublico ? (int) in_array(FormasContratacaoPoderPublico::TERMO_FOMENTO, $record->formasContratacaoPoderPublico) : '', // 37	Termo de fomento (Lei n?? 13.019/2014)
            $record->formasContratacaoPoderPublico ? (int) in_array(FormasContratacaoPoderPublico::ACORDO_COOPERACAO, $record->formasContratacaoPoderPublico) : '', // 38	Acordo de coopera????o (Lei n?? 13.019/2014)
            $record->formasContratacaoPoderPublico ? (int) in_array(FormasContratacaoPoderPublico::CONTRATO_PRESTACAO_SERVICO, $record->formasContratacaoPoderPublico) : '', // 39	Contrato de presta????o de servi??o
            $record->formasContratacaoPoderPublico ? (int) in_array(FormasContratacaoPoderPublico::TERMO_COOPERACAO_TECNICA, $record->formasContratacaoPoderPublico) : '', // 40	Termo de coopera????o t??cnica e financeira
            $record->formasContratacaoPoderPublico ? (int) in_array(FormasContratacaoPoderPublico::CONTRATO_CONSORCIO, $record->formasContratacaoPoderPublico) : '', // 41	Contrato de cons??rcio p??blico/Conv??nio de coopera????o
            $record->qtdMatAtividadesComplentar, // 42	Atividade complementar
            $record->qtdMatAee, // 43	Atendimento educacional especializado
            $record->qtdMatCrecheParcial, // 44	Ensino Regular - Creche - Parcial
            $record->qtdMatCrecheIntegral, // 45	Ensino Regular - Creche - Integral
            $record->qtdMatPreEscolaParcial, // 46	Ensino Regular - Pr??-escola - Parcial
            $record->qtdMatPreEscolaIntegral, // 47	Ensino Regular - Pr??-escola - Integral
            $record->qtdMatFundamentalIniciaisParcial, // 48	Ensino Regular - Ensino Fundamental - Anos Iniciais - Parcial
            $record->qtdMatFundamentalIniciaisIntegral, // 49	Ensino Regular - Ensino Fundamental - Anos Iniciais - Integral
            $record->qtdMatFundamentalFinaisParcial, // 50	Ensino Regular - Ensino Fundamental - Anos Finais - Parcial
            $record->qtdMatFundamentalFinaisIntegral, // 51	Ensino Regular - Ensino Fundamental - Anos Finais - Integral
            $record->qtdMatEnsinoMedioParcial, // 52	Ensino Regular - Ensino M??dio - Parcial
            $record->qtdMatEnsinoMedioIntegral, // 53	Ensino Regular - Ensino M??dio - Integral
            $record->qdtMatClasseEspecialParcial, // 54	Educa????o Especial - Classe especial - Parcial
            $record->qdtMatClasseEspecialIntegral, // 55	Educa????o Especial - Classe especial - Integral
            $record->qdtMatEjaFundamental, // 56	Educa????o de Jovens e Adultos (EJA) - Ensino fundamental
            $record->qtdMatEjaEnsinoMedio, // 57	Educa????o de Jovens e Adultos (EJA) - Ensino m??dio
            $record->qtdMatEdProfIntegradaEjaFundamentalParcial, // 58	Educa????o Profissional - Qualifica????o profissional - Integrada ?? educa????o de jovens e adultos no ensino fundamental - Parcial
            $record->qtdMatEdProfIntegradaEjaFundamentalIntegral, // 59	Educa????o Profissional - Qualifica????o profissional - Integrada ?? educa????o de jovens e adultos no ensino fundamental - Integral
            $record->qtdMatEdProfIntegradaEjaNivelMedioParcial, // 60	Educa????o Profissional - Qualifica????o profissional t??cnica - Integrada ?? educa????o de jovens e adultos de n??vel m??dio - Parcial
            $record->qtdMatEdProfIntegradaEjaNivelMedioIntegral, // 61	Educa????o Profissional - Qualifica????o profissional t??cnica - Integrada ?? educa????o de jovens e adultos de n??vel m??dio - Integral
            $record->qtdMatEdProfConcomitanteEjaNivelMedioParcial, // 62	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante ?? educa????o de jovens e adultos de n??vel m??dio - Parcial
            $record->qtdMatEdProfConcomitanteEjaNivelMedioIntegral, // 63	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante ?? educa????o de jovens e adultos de n??vel m??dio - Integral
            $record->qtdMatEdProfIntercomentarEjaNivelMedioParcial, // 64	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante intercomplementar ?? educa????o de jovens e adultos de n??vel m??dio - Parcial
            $record->qtdMatEdProfIntercomentarEjaNivelMedioIntegral, // 65	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante intercomplementar ?? educa????o de jovens e adultos de n??vel m??dio - Integral
            $record->qtdMatEdProfIntegradaEnsinoMedioParcial, // 66	Educa????o Profissional - Qualifica????o profissional t??cnica - Integrada ao ensino m??dio - Parcial
            $record->qtdMatEdProfIntegradaEnsinoMedioIntegral, // 67	Educa????o Profissional - Qualifica????o profissional t??cnica - Integrada ao ensino m??dio - Integral
            $record->qtdMatEdProfConcomitenteEnsinoMedioParcial, // 68	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante ao ensino m??dio - Parcial
            $record->qtdMatEdProfConcomitenteEnsinoMedioIntegral, // 69	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante ao ensino m??dio - Integral
            $record->qtdMatEdProfIntercomplementarEnsinoMedioParcial, // 70	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante intercomplementar ao ensino m??dio - Parcial
            $record->qtdMatEdProfIntercomplementarEnsinoMedioIntegral, // 71	Educa????o Profissional - Qualifica????o profissional t??cnica - Concomitante intercomplementar ao ensino m??dio - Integral
            $record->qtdMatEdProfTecnicaIntegradaEnsinoMedioParcial, // 72	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Integrada ao ensino m??dio - Parcial
            $record->qtdMatEdProfTecnicaIntegradaEnsinoMedioIntegral, // 73	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Integrada ao ensino m??dio - Integral
            $record->qtdMatEdProfTecnicaConcomitanteEnsinoMedioParcial, // 74	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante ao ensino m??dio - Parcial
            $record->qtdMatEdProfTecnicaConcomitanteEnsinoMedioIntegral, // 75	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante ao ensino m??dio - Integral
            $record->qtdMatEdProfTecnicaIntercomplementarEnsinoMedioParcial, // 76	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante intercomplementar ao ensino m??dio - Parcial
            $record->qtdMatEdProfTecnicaIntercomplementarEnsinoMedioItegral, // 77	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante intercomplementar ao ensino m??dio - Integral
            $record->qtdMatEdProfTecnicaSubsequenteEnsinoMedio, // 78	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Subsequente ao ensino m??dio
            $record->qtdMatEdProfTecnicaIntegradaEjaNivelMedioParcial, // 79	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Integrada ?? educa????o de jovens e adultos de n??vel m??dio - Parcial
            $record->qtdMatEdProfTecnicaIntegradaEjaNivelMedioIntegral, // 80	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Integrada ?? educa????o de jovens e adultos de n??vel m??dio - Integral
            $record->qtdMatEdProfTecnicaConcomitanteEjaNivelMedioParcial, // 81	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante ?? educa????o de jovens e adultos de n??vel m??dio - Parcial
            $record->qtdMatEdProfTecnicaConcomitanteEjaNivelMedioIntegral, // 82	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante ?? educa????o de jovens e adultos de n??vel m??dio - Integral
            $record->qtdMatEdProfTecnicaIntercomplementarEjaNivelMedioParcial, // 83	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante intercomplementar ?? educa????o de jovens e adultos de n??vel m??dio - Parcial
            $record->qtdMatEdProfTecnicaIntercomplementarEjaNivelMedioIntegral, // 84	Educa????o Profissional - Educa????o profissional t??cnica de n??vel m??dio - Concomitante intercomplementar ?? educa????o de jovens e adultos de n??vel m??dio - Integral
            $record->cnpjMantenedoraPrincipal, // 85	CNPJ da mantenedora principal da escola privada
            $record->cnpjEscolaPrivada, // 86	N??mero do CNPJ da escola privada
            $record->regulamentacao, // 87	Regulamenta????o/autoriza????o no conselho ou ??rg??o municipal, estadual ou federal de educa????of
            $record->esferaFederal, // 88	Federal
            $record->esferaEstadual, // 89	Estadual
            $record->esferaMunicipal, // 90	Municipal
            $record->unidadeVinculada, // 91	Unidade vinculada ?? escola de educa????o b??sica ou unidade ofertante de educa????o superior
            $record->inepEscolaSede, // 92	C??digo da Escola Sede
            $record->codigoIes, // 93	C??digo da IES
        ];
    }

    private function processData($data)
    {
        $data->codigoInep = substr($data->codigoInep, 0, 8);
        $data->inicioAnoLetivo = Portabilis_Date_Utils::pgSQLToBr($data->inicioAnoLetivo);
        $data->fimAnoLetivo = Portabilis_Date_Utils::pgSQLToBr($data->fimAnoLetivo);
        $data->nome = $this->convertStringToCenso($data->nome);
        $data->logradouro = $this->convertStringToCenso($data->logradouro);
        $data->numero = $this->convertStringToCenso($data->numero);
        $data->complemento = $this->convertStringToCenso($data->complemento);
        $data->bairro = $this->convertStringToCenso($data->bairro);
        $data->email = mb_strtoupper($data->email);
        $data->orgaoRegional = ($data->orgaoRegional ? str_pad($data->orgaoRegional, 5, '0', STR_PAD_LEFT) : null);
        $data->cnpjEscolaPrivada = $this->cnpjToCenso($data->cnpjEscolaPrivada);
        $data->cnpjMantenedoraPrincipal = $this->cnpjToCenso($data->cnpjMantenedoraPrincipal);
        $data->poderPublicoConveniado = Portabilis_Utils_Database::pgArrayToArray($data->poderPublicoConveniado);
        $data->formasContratacaoPoderPublico = Portabilis_Utils_Database::pgArrayToArray($data->formasContratacaoPoderPublico);

        return $data;
    }

    /**
     * @param $data
     */
    protected function hydrateModel($data)
    {
        $model = clone $this->model;
        foreach ($data as $field => $value) {
            if (property_exists($model, $field)) {
                $model->$field = $value;
            }
        }

        return $model;
    }
}
