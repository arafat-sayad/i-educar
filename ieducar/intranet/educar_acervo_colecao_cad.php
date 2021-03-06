<?php

return new class extends clsCadastro {
    /**
     * Referencia pega da session para o idpes do usuario atual
     *
     * @var int
     */
    public $pessoa_logada;

    public $cod_acervo_colecao;
    public $ref_usuario_exc;
    public $ref_usuario_cad;
    public $nm_colecao;
    public $descricao;
    public $data_cadastro;
    public $data_exclusao;
    public $ativo;
    public $ref_cod_biblioteca;

    public function Inicializar()
    {
        $retorno = 'Novo';

        $this->cod_acervo_colecao=$_GET['cod_acervo_colecao'];

        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(593, $this->pessoa_logada, 11, 'educar_acervo_colecao_lst.php');

        if (is_numeric($this->cod_acervo_colecao)) {
            $obj = new clsPmieducarAcervoColecao($this->cod_acervo_colecao);
            $registro  = $obj->detalhe();
            if ($registro) {
                foreach ($registro as $campo => $val) {  // passa todos os valores obtidos no registro para atributos do objeto
                    $this->$campo = $val;
                }

                $obj_obra = new clsPmieducarAcervoColecao($this->cod_acervo_colecao);
                $det_obra = $obj_obra->detalhe();

                $obj_biblioteca = new clsPmieducarBiblioteca($det_obra['ref_cod_biblioteca']);
                $obj_det = $obj_biblioteca->detalhe();

                $this->ref_cod_instituicao = $obj_det['ref_cod_instituicao'];
                $this->ref_cod_escola = $obj_det['ref_cod_escola'];
                $this->ref_cod_biblioteca = $obj_det['cod_biblioteca'];

                $obj_permissoes = new clsPermissoes();
                if ($obj_permissoes->permissao_excluir(593, $this->pessoa_logada, 11)) {
                    $this->fexcluir = true;
                }

                $retorno = 'Editar';
            }
        }
        $this->url_cancelar = ($retorno == 'Editar') ? "educar_acervo_colecao_det.php?cod_acervo_colecao={$registro['cod_acervo_colecao']}" : 'educar_acervo_colecao_lst.php';
        $this->nome_url_cancelar = 'Cancelar';

        $nomeMenu = $retorno == 'Editar' ? $retorno : 'Cadastrar';

        $this->breadcrumb($nomeMenu . ' cole????o', [
            url('intranet/educar_biblioteca_index.php') => 'Biblioteca',
        ]);

        return $retorno;
    }

    public function Gerar()
    {

        // primary keys
        $this->campoOculto('cod_acervo_colecao', $this->cod_acervo_colecao);

        $get_escola     = 1;
        $escola_obrigatorio = false;
        $get_biblioteca = 1;
        $instituicao_obrigatorio = true;
        $biblioteca_obrigatorio = true;
        include('include/pmieducar/educar_campo_lista.php');

        // text
        $this->campoTexto('nm_colecao', 'Cole????o', $this->nm_colecao, 30, 255, true);
        $this->campoMemo('descricao', 'Descri????o', $this->descricao, 60, 5, false);
    }

    public function Novo()
    {
        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(593, $this->pessoa_logada, 11, 'educar_acervo_colecao_lst.php');

        $obj = new clsPmieducarAcervoColecao($this->cod_acervo_colecao, $this->pessoa_logada, $this->pessoa_logada, $this->nm_colecao, $this->descricao, $this->data_cadastro, $this->data_exclusao, $this->ativo, $this->ref_cod_biblioteca);
        $this->cod_acervo_colecao = $cadastrou = $obj->cadastra();
        if ($cadastrou) {
            $obj->cod_acervo_colecao = $this->cod_acervo_colecao;
            $this->mensagem .= 'Cadastro efetuado com sucesso.<br>';

            $this->simpleRedirect('educar_acervo_colecao_lst.php');
        }

        $this->mensagem = 'Cadastro n??o realizado.<br>';

        return false;
    }

    public function Editar()
    {
        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(593, $this->pessoa_logada, 11, 'educar_acervo_colecao_lst.php');

        $obj = new clsPmieducarAcervoColecao($this->cod_acervo_colecao, $this->pessoa_logada, $this->pessoa_logada, $this->nm_colecao, $this->descricao, $this->data_cadastro, $this->data_exclusao, $this->ativo, $this->ref_cod_biblioteca);
        $editou = $obj->edita();
        if ($editou) {
            $this->mensagem .= 'Edi????o efetuada com sucesso.<br>';

            $this->simpleRedirect('educar_acervo_colecao_lst.php');
        }

        $this->mensagem = 'Edi????o n??o realizada.<br>';

        return false;
    }

    public function Excluir()
    {
        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_excluir(593, $this->pessoa_logada, 11, 'educar_acervo_colecao_lst.php');

        $obj = new clsPmieducarAcervoColecao($this->cod_acervo_colecao, $this->pessoa_logada, $this->pessoa_logada, $this->nm_colecao, $this->descricao, $this->data_cadastro, $this->data_exclusao, 0);
        $excluiu = $obj->excluir();
        if ($excluiu) {
            $this->mensagem .= 'Exclus??o efetuada com sucesso.<br>';

            $this->simpleRedirect('educar_acervo_colecao_lst.php');
        }

        $this->mensagem = 'Exclus??o n??o realizada.<br>';

        return false;
    }

    public function Formular()
    {
        $this->title = 'Cole????o';
        $this->processoAp = '593';
    }
};
