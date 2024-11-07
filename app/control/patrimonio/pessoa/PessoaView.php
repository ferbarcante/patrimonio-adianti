<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Util\TTextDisplay;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;

class PessoaView extends TPage
{

    private $datagrid; 
    private static $repository = 'Pessoa';

     public function __construct()
    {
        parent::__construct();

      
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->enablePopover('Details', '<b>Nome:</b> {nome} <br> <b> Cpf:</b> 
        {cpf}');

        // parametros: nome usado na fonte de dados, titulo exibido, alinhamento, larguta da coluna 
        $nome = new TDataGridColumn('nm_pessoa' ,'Pessoa' , 'center', '10%');
        $cpf = new TDataGridColumn('nu_cpf','Cpf', 'left', '10%');
        $email = new TDataGridColumn('ds_email','Email', 'left', '10%');

        // futuramente adicionar os setores pelo qual a pessoa é responsável

        //adiciona as colunas
        $this->datagrid->addColumn($nome, new TAction([$this, 'onColumnAction'], ['column' => 'nm_pessoa']));
        $this->datagrid->addColumn($cpf, new TAction([$this, 'onColumnAction'], ['column' => 'nu_cpf']));
        $this->datagrid->addColumn($email, new TAction([$this, 'onColumnAction'], ['column' => 'ds_email']));

        $nome->title  = 'Aqui está o nome do responsável';
        $cpf->title  = 'Aqui está o cpf do responsável';
               
        // creates two datagrid actions
        // chama o método onView e OnDelete e define quais parametros serão passados a esse método
        //$action1 = new TDataGridAction([$this, 'onView']);
        //$action2 = new TDataGridAction([$this, 'onDelete']);
 
        // custom button presentation
        // $action1->setUseButton(TRUE);
        // $action2->setUseButton(TRUE);

        // adiciona as ações ao dataview
        // $this->datagrid->addAction($action1, 'View', 'fa:search blue');
        // $this->datagrid->addAction($action2, 'Delete', 'far:trash-alt red');
        
        $this->datagrid->createModel();
        
        $addPessoa = new TButton('addPessoa');
        $addPessoa->setLabel('Criar Pessoa');
        $addPessoa->addFunction("__adianti_load_page('index.php?class=PessoaForm');");
        
        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->add($addPessoa);
      
        $a = new TTextDisplay('Lista de pessoas responsáveis:');
        $a->style = 'text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2); sans-serif; font-size: 2.5rem; font-weight: bold; text-align: center; padding: 20px; margin: 0;';
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($a);
        $vbox->add($panel);
        parent::add($vbox);
    }

    function onReload($param = NULL)
    {
        var_dump($param);
        try 
        {
            $this->datagrid->clear();
            TTransaction::open('patrimonio');

            $repository = new TRepository(self::$repository);
            // $limit = 10;
            $criteria = new TCriteria;
    
            if(empty($param['order']))
            {
                $param['order'] = 'id_pessoa';
                $param['direction'] = 'asc';
            } 
    
            $criteria->setProperties($param);
           // $criteria->setProperty('limit', $limit);
    
           /* 
           verifica se o usuário preencheu um filtro
           
           if (TSession::getValue('exemplo_filtro')){
                //add filtro 
                $criteria->add(TSession::getValue('exemplo_filtro'));
           }
           */
    
           $objects = $repository->load($criteria);
    
           if($objects)
           {
                foreach ($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
           }
            
           $criteria->resetProperties();
           $count = $repository->count($criteria);
    
        //    $this->pageNavigation->setCount($count);
        //    $this->pageNavigation->setProperties($param);
        //    $this->pageNavigation->setLimit($limit);

        }  catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } finally 
        {
            TTransaction::close();
        }
    }

     /**
     * Executed when the user clicks at the column title
     */
    public function onColumnAction($param)
    {
        // get the parameter and shows the message
        $key = $param['column'];
        new TMessage('info', "Você clicou na coluna <b>{$key}</b>");
    }

     /**
     * Executed when the user clicks at the view button
     */
    public function onView($param)
    {
        // get the parameter and shows the message
        $evento = $param['nm_evento'];
        $data = $param['dt_evento'];
        $local = $param['nm_local'];
        new TMessage('info', "O evento <b>$evento</b> irá ocorrer em <b>$local</b> na data <b>$data</b>");
    }

     /**
     * Executed when the user clicks at the delete button
     * STATIC Method, does't reload the page when executed
     */
    public static function onDelete($param)
    {
        // get the parameter and shows the message
        $evento = $param['nm_evento'];
        new TMessage('error', "O evento <b>{$evento}</b> não pôde ser deletado!");
    }

    /**
     * shows the page
     */
    function show()
    {
        $this->onReload();
        parent::show();
    }
}