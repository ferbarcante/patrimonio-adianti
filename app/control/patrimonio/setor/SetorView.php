<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class SetorView extends TPage
{
    private $form;
    private $datagrid;
    private $pageNavigation;
    private static $repository = 'Setor';


    function __construct()
    {   
        parent::__construct();
        
        $this->setDatabase('setor');
        $this->setActiveRecord('Setor');
        
        $this->form = new BootstrapFormBuilder('form_search_setor');
        $this->form->setFormTitle('Procurar Setor:');

        $nome = new TEntry('nome');
        $nm_pessoa = new TEntry('nm_pessoa');
        $codigo = new TEntry('codigo');
        $cidade = new TEntry('endereco->nm_cidade');

        $this->form->addFields([new TLabel('Nome:')], [$nome]);
        $this->form->addFields([new TLabel('Responsável:')], [$nm_pessoa]);
        $this->form->addFields([new TLabel('Código:')], [$codigo]);
        $this->form->addFields([new TLabel('Cidade:')], [$cidade]);

        $this->form->addAction('Procurar', new TAction([$this, 'onSearch']), 'fa:search black');
        $this->form->addActionLink('Novo', new TAction(['SetorForm','onClear']),'fa:plus-circle black');
        $this->form->addActionLink('Limpar', new TAction([$this, 'clear']), 'fa:eraser red');

        $this->form->setData(TSession::getValue('SetorView_filter_data'));

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        
        
        $codigo = new TDataGridColumn('cd_setor', 'Código', 'right', '10%');
        $nomeSetor = new TDataGridColumn('nm_setor', 'Nome', 'center', '60%');
        $endereco = new TDataGridColumn('endereco->nm_cidade', 'Endereço', 'left', '30%');
        $responsavel = new TDataGridColumn('pessoa->nm_pessoa', 'Responsável', 'left', '30%');

        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nomeSetor);
        $this->datagrid->addColumn($endereco);
        $this->datagrid->addColumn($responsavel);

        $editar = new TDataGridAction(['SetorVIew', 'onEdit']);
        $deletar = new TDataGridAction([$this, 'onDelete']);

        $this->datagrid->addAction($editar, 'Editar',  'far:edit black');
        $this->datagrid->addAction($deletar, 'Deletar', 'far:trash-alt black');

        $this->datagrid->createModel();

        $this->form = new TForm('form_search_customer');
        // adiciona datagrid dentro do formulario
        $this->form->add($this->datagrid);
        $this->form->style = 'overflow-x:auto';

        $codigo = new TEntry('codigo');
        $nome = new TEntry('nome');
        $responsavel = new TEntry('responsavel');
        $cidade = new TEntry('cidade');
       
        // $logradouro = new TEntry('logradouro');
        // $bairro = new TEntry('bairro');
        // $cidade = new TEntry('cidade');
        // $uf = new TEntry('uf');
        // $complemento = new TEntry('complemento');

        $codigo->exitOnEnter();
        $nome->exitOnEnter();
        $cidade->exitOnEnter();


    } 

    function onReload($param = NULL)
    {
        try 
        {
            TTransaction::open('setor');
            $repository = new TRepository(self::$repository);
            $criteria = new TCriteria;
    
            if(empty($param['order']))
            {
                $param['order'] = 'id_setor';
                $param['direction'] = 'asc';
            } 
        } catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } finally
        {
            TTransaction::close();
        }
    }

    function onSearch()
    {
        $data = $this->form->getData();
        $filter[];

        if (isset($data->codigo) && ($data->nome = '') && ($data->nm_pessoa = '') && ($data->cidade = ''))
        {
            $filter[] = new TFilter('cd_setor', 'like', "%{$data->codigo}%");

            TSession::setValue('')
        }
    }

    function onEdit($param = NULL)
    {
        try 
        {
            if(isset($param['id_setor']))
            {
                $key = $param['id_setor'];
                TTransaction::open('setor');
                $setor = new Setor($key);
                $this->form->setData($setor);
                $this->onReload();
            }
        } catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } finally 
        {
            TTransaction::close();
        }
    }

    function onDelete($param = NULL){
        
        try 
        {  
            TTransaction::open('setor');

            $action = new TAction([__CLASS__,'Delete']);
            $action->setParameters($param);
            new TQuestion('Deseja realmente excluir o setor?', $action);
            
            $key = $param['id_setor'];
            $setor = new Setor($key);
            $setor->delete();
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', 'Registro excluído', $pos_action);
        } catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } finally 
        {
            TTransaction::close();
        }
}