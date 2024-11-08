<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TActionLink;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Wrapper\BootstrapFormBuilder;

class SetorForm extends TPage
{
    private $form;

    public function __construct()
    {
        parent::__construct();
        
        // $this->embedded = $embedded;
        
        if (!$this->embedded)
        {
            parent::setTargetContainer('adianti_right_panel');
        }

        $this->form = new BootstrapFormBuilder('form_setor');
        
        if (!$this->embedded)
        {
            $this->form->setFormTitle('Setor');
        }

        $this->form->setClientValidation(true);
        // cria os campos do formulário
        $codigo          = new TEntry('codigo');
        $nome = new TEntry('nome');
        $responsavel       = new TDBCombo('id_pessoa', 'patrimonio', 'Pessoa', 'id_pessoa', 'nm_pessoa');
        $responsavel->setSize('40%');

        $button = new TActionLink('', new TAction(['PessoaWidow', 'onClear']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = 'Novo';
        $responsavel->after($button);

        $this->form->appendPage('Dados básicos');

        $this->form->addFields([new TLabel('Código (*)', '#FF0000')], [$codigo], [new TLabel('Nome (*)', '#FF0000')], [$nome]);
        $this->form->addFields([new TLabel('Responsável (*)', '#FF0000')], [$responsavel]);
       
        $this->form->appendPage('Endereço');
    
        $cep  = new TEntry('cep');
        $uf       = new TCombo('uf');
        $cidade  = new TEntry('cidade');
        $logradouro      = new TEntry('logradouro');
        $complemento = new TEntry('complemento');
        $uf->addItems(['ba', 'sp', 'bh', 'sc']);
        $cep->setMask('99999-999');

        $this->form->addFields([new TLabel('Cep')], [$cep]);
        $this->form->addFields([new TLabel('Uf')], [$uf]);
        $this->form->addFields([new TLabel('Cidade')], [$cidade]);
        $this->form->addFields([new TLabel('Logradouro')], [$logradouro]);
        $this->form->addFields([new TLabel('Complemento')], [$complemento]);

        $uf->setSize('30%');

        // validações
        $codigo->addValidation('Código', new TRequiredValidator);
        $nome->addValidation('Nome', new TRequiredValidator);
        $responsavel->addValidation('Responsável', new TRequiredValidator);
        $cep->addValidation('Cep', new TRequiredValidator);
        $uf->addValidation('Uf', new TRequiredValidator);
        $cidade->addValidation('Cidade', new TRequiredValidator);
        $logradouro->addValidation('Logradouro', new TRequiredValidator);
        $complemento->addValidation('Complemento', new TRequiredValidator);

        // add ações
        $this->form->addAction( 'Save', new TAction([$this, 'onSave']), 'fa:save green' );        
        $this->form->addActionLink('Limpar formulário', new TAction([$this, 'onEdit']), 'fa:erase blue');
        $this->form->addHeaderActionLink('Fechar', new TAction([$this, 'onClose']), 'fa:times red');

        parent::add($this->form);
    }

    public function onSave()
    {
        try 
        {
            TTransaction::open('patrimonio');
            $data = $this->form->getData();

            $setor = new Setor;
            $setor->fromArray((array)$data);
            
            $setor->cd_setor = $data->codigo;
            $setor->nm_setor = $data->nome;
            // $setor->id_pessoa = $data->id_pessoa;
            // $setor->store();
            
        } catch (Exception $e)
        {
            $erro = $e->getMessage();
        } finally 
        {
            TTransaction::close();
        }
    }

    public function onEdit()
    {

    }

    public function onClear()
    {

    }

    public function onClose()
    {

    }

    public function onShow()
    {

    }
}