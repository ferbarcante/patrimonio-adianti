<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Util\TImage;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;

class PessoaForm extends TPage
{
    private $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle("Cadastro de Pessoa Responsável");
        $this->form->generateAria();

        $nome = new TEntry('nome');
        $cpf = new TEntry('cpf');
        $email = new TEntry('email');

        $email->setInnerIcon(new TImage('fa:email blue'));
        //esse fa pode dar errado!!!
        
        $cpf->setMask('999.999.999-99');
        
        $this->form->addFields([new TLabel('Nome:')], [$nome]);
        $this->form->addFields([new TLabel('Cpf:')], [$cpf]);
        $this->form->addFields([new TLabel('Email:')], [$email]);

        $this->form->addAction('Send', new TAction(array($this, 'onSend')), 'far:check-circle green');
        // extra dropdown.
        // $dropdown = new TDropDown('Dropdown test', 'fa:th blue');
        // $dropdown->addPostAction( 'PostAction', new TAction(array($this, 'onSend') ), $this->form->getName(), 'far:check-circle');
        // $dropdown->addAction( 'Lista de Pessoas Responsáveis', new TAction(array('PessoaView', 'onReload') ), 'fa:link');

        // $this->form->addFooterWidget($dropdown);
        // $this->form->addHeaderWidget($dropdown);

        $botaoVoltar = new TButton('voltar', new TImage());
        $botaoVoltar->setLabel('Voltar');
        $botaoVoltar->addFunction("__adianti_load_page('index.php?class=PessoaView');");
        $botaoVoltar->setImage('fas:arrow-alt-circle-left blue');
        $this->form->addFooterWidget($botaoVoltar);

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__ ));
        $vbox->add($this->form);

        parent::add($vbox);
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
            new TMessage('info', 'deu certo!!!');
        } catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } finally 
        {
            TTransaction::close();
        }
        
    }
}