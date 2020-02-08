<?php

use Adianti\Registry\TSession;

class EmpresaViewForm extends TPage
{
    protected $form; // registration form
    protected $datagrid; // listing
    
    // trait com onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;

    function __construct()
    {
        parent::__construct();

        $this->adianti_target_container = 'adianti_right_panel';

        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('Empresa');
        $this->form->generateAria(); // automatic aria-label

        $situacaoCadastralControler = new SituacaoCadastralEmpresa();
        $listSituacaoCadastral = $situacaoCadastralControler->getList();
        
        $cnpjLabel = 'CNPJ';
        $formDinCnpjField = new TFormDinCnpjField('cnpj',$cnpjLabel);
        $cnpj = $formDinCnpjField->getAdiantiObj();

        $comboSituacao  = new TCombo('motivo_situacao');
        $comboSituacao->addItems($listSituacaoCadastral);

        $razao_social = new TEntry('razao_social');
        $nome_fantasia = new TEntry('nome_fantasia');

        $this->form->addFields( [new TLabel($cnpjLabel)],[$cnpj]);
        $this->form->addFields( [new TLabel('Motivo Situação')], [$comboSituacao]);
        $this->form->addFields( [new TLabel('Razão Social')],[$razao_social]);
        $this->form->addFields( [new TLabel('Nome Fantasia')], [$nome_fantasia] );
        

        $this->form->addHeaderActionLink('Fechar',  new TAction([$this, 'onClose']), 'fa:times red');

        // add the table inside the page
        parent::add($this->form);
    }

    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    function onView($param)
    {
        try{
            // abre transação com a base de dados
            TTransaction::open('cnpj_full');
            $empresa = new Empresa($param['key']);
            $this->form->setData($empresa);
            $this->showGridSocios($empresa->getSocios());
            TTransaction::close(); // fecha a transação
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    function showGridSocios($socios){
        // create the datagrid
        $listSocios = new BootstrapDatagridWrapper(new TDataGrid);
        $listSocios->width = '100%';    
        $listSocios->addColumn(new TDataGridColumn('nome_socio', 'Nome', 'left'));
        $listSocios->addColumn(new TDataGridColumn('cnpj_cpf_socio', 'CPF', 'left'));
        $listSocios->createModel();        
        $listSocios->addItems($socios);
        $panel = TPanelGroup::pack('Lista de Socios', $listSocios);
        parent::add($panel);
    }
}
