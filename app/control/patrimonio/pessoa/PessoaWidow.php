<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Database\TTransaction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Dialog\TAlert;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Menu\TMenu;
use Adianti\Wrapper\BootstrapFormBuilder;

class PessoaWidow extends TWindow
{
    protected $form; // form
 
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_pessoa');
        
        // create the form fields
        $id       = new THidden('id');
        $name     = new TEntry('name');
        $cpf = new TEntry('cpf');
        $email = new TEntry('email');

        $cpf->setMask('999.999.999-99');
        $id->setEditable(FALSE);
        
        // add the form fields
        $this->form->addFields( [$id] );
        $this->form->addFields( [new TLabel('Nome', 'red')], [$name] );
        $this->form->addFields( [new TLabel('Cpf', 'red')], [$cpf] );
        $this->form->addFields( [new TLabel('Email', 'red')], [$email]);
        
        $name->addValidation( 'Name', new TRequiredValidator);
        $cpf->addValidation( 'Cpf', new TRequiredValidator);
        
        // define the form action
        $this->form->addAction('Save', new TAction(array($this, 'onSave')), 'fa:save green');
        
        parent::add($this->form);
    }

    public function onSend($param)
    {   
        try 
        {
            TTransaction::open('patrimonio');

            $data = $this->form->getData();
            
            $pessoa = new Pessoa();

            $cpfCaracteres = $data->cpf;
            $data->cpf = preg_replace('/\D/','', $cpfCaracteres);
            $pessoa->nu_cpf = $data->cpf;
            $pessoa->ds_email = $data->email;
            $pessoa->nm_pessoa = $data->nome;

            $pessoa->store(); 
            new TMenu('info', 'deu certo!!!');
        } catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } finally 
        {
            TTransaction::close();
        }
        
    }

    function onClear($param)
    {
        $this->form->clear();
    }

    function onSave()
    {
    }

    function onEdit()
    {
    }

    function onClose()
    {
    }

}