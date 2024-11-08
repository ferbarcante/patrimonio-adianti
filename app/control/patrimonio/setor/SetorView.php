<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TPanel;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Util\TXMLBreadCrumb;
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
        
        // $this->setDatabase('setor');
        // $this->setActiveRecord('Setor');
        
        $this->form = new BootstrapFormBuilder('form_search_setor');
        $this->form->setFormTitle('Procurar Setor:');

        $nome = new TEntry('nome');
        $nm_pessoa = new TEntry('nm_pessoa');
        $codigo = new TEntry('codigo');
        $cidade = new TEntry('nm_cidade');

        // // campos do filtro
        // $this->form->addFields([new TLabel('Nome')], [$nome]);
        // $this->form->addFields([new TLabel('Responsável')], [$nm_pessoa]);
        // $this->form->addFields([new TLabel('Código')], [$codigo]);
        // $this->form->addFields([new TLabel('Cidade')], [$cidade]);

        $nome->exitOnEnter();
        $codigo->exitOnEnter();
        $nm_pessoa->exitOnEnter();
        $cidade->exitOnEnter();

        $codigo->setSize('100%');
        $nome->setSize('100%');
        $nm_pessoa->setSize('100%');
        $cidade->setSize('100%');

        $codigo->tabindex = -1;
        $nome->tabindex = -1;
        $cidade->tabindex = -1;
        $nm_pessoa->tabindex = -1;
        // define ação de saída dos campos para busca de registros
        $codigo->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $nome->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $nm_pessoa->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );
        $cidade->setExitAction( new TAction([$this, 'onSearch'], ['static'=>'1']) );

        $datagrid_form = new TForm('formulario');

        // cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        
        $colCodigo = new TDataGridColumn('cd_setor', 'Código', 'center', '10%');
        $colNomeSetor = new TDataGridColumn('nm_setor', 'Nome', 'left', '28%');
        $colEndereco = new TDataGridColumn('endereco->nm_cidade', 'Cidade', 'left', '28%');
        $colResponsavel = new TDataGridColumn('pessoa->nm_pessoa', 'Responsável', 'left', '28%');

        // $colCodigo->setAction(new TAction([$this, 'onReload']), ['order' => 'cd_setor']);
        // $colNomeSetor->setAction(new TAction([$this,'onReload']), ['order'=> 'nm_setor']);
        // $colResponsavel->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa->nm_pessoa']);
        // $colEndereco->setAction(new TAction([$this, 'onReload']), ['order' => 'endereco->nm_cidade']);

        

        $this->datagrid->addColumn($colCodigo);
        $this->datagrid->addColumn($colNomeSetor);
        $this->datagrid->addColumn($colEndereco);
        $this->datagrid->addColumn($colResponsavel);

        $editar = new TDataGridAction([ $this, 'onEdit'], ['id_setor' => '{id}']);
        $deletar = new TDataGridAction([$this, 'onDelete'], ['id_setor'=>'{id}']);

        $this->datagrid->addAction($editar, 'Editar',  'far:edit black');
        $this->datagrid->addAction($deletar, 'Deletar', 'far:trash-alt black');

        $this->datagrid->createModel();

        // adiciona datagrid dentro do formulario
        $this->form->add($this->datagrid);
        $this->form->style = 'overflow-x:auto';

        // cria linha com os inputs de busca 
        $tr = new TElement('tr');
        $this->datagrid->prependRow($tr);

        $tr->add(TElement::tag('td', ''));
        $tr->add(TElement::tag('td', ''));
        $tr->add(TElement::tag('td', $codigo));
        $tr->add(TElement::tag('td', $nome));
        $tr->add(TElement::tag('td', $cidade));
        $tr->add(TElement::tag('td', $nm_pessoa));
        
        $this->form->addField($codigo);
        $this->form->addField($nome);
        $this->form->addField($cidade);
        $this->form->addField($nm_pessoa);
        
        $this->form->setData(TSession::getValue(__CLASS__, '_filter_data'));

        // cria o paginador
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        $this->pageNavigation->enableCounters();

        // $this->cadastrar = new TButton('tbutton cadsatrar');

        // encapsula o form e paginador dentro de um painel
        $panel = new TPanelGroup('Lista de Setores');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        // $panel->addFooter($this->cadastrar);
        
        // menu flutuante com opções de exportação
        $dropdown = new TDropDown('Export', 'fa:list black');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction('Salvar como PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false']), 'fa:file-pdf black');
        
        // $dropdown = new TDropDown('Export', 'fa:list black');
        // $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        // $dropdown->addAction('Salvar como PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false']), 'fa:file-pdf black');
        
        $panel->addHeaderWidget($dropdown );
        $panel->addHeaderActionLink('Novo', new TAction(['SetorForm', 'onEdit'],['register_state' => 'false']), 'fa:plus black' );

        // caixa vertical para empacotar tudo
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);

        parent::add($vbox);
    } 

    function onReload($param = NULL)
    {
        try 
        {
            TTransaction::open('patrimonio');
            $repository = new TRepository(self::$repository);
            $criteria = new TCriteria;
            $limit = 10;

            if(empty($param['order']))
            {
                $param['order'] = 'id_setor';
                $param['direction'] = 'asc';
            } 
            
            // configura o critério com base nos parâmetros da URL
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            // verifica se usuário preencheu um filtro
            
            if (TSession::getValue('Setor_filter'))
            {
                // TODO: FAZER FOREACH PARA ACESSAR O ARRAY DO SETOR_FILTER
                $criteria->add(TSession::getValue('Setor_filter'));
            }
            
            // carrega os objetos conforme o critério de seleção
            $objects = $repository->load($criteria);
            $criteria->setProperties($param);
            $objects = $repository->load($criteria);
            $this->datagrid->clear();


            if($objects)
            {
                foreach ($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
            }

             // reset nos critérios (limit, offset)
             $criteria->resetProperties();
             $count = $repository->count($criteria);
             
             $this->pageNavigation->setCount($count); // qtde registros
             $this->pageNavigation->setProperties($param); // order, page
             $this->pageNavigation->setLimit($limit); // limit
             
             $this->loaded = true;

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
        $filters[] = null;

        TSession::setValue('Setor_filter', NULL);

        $array = ['ncd_setor', 'nm_setor'];

        foreach($array as $item)
        {
            if (isset($data->$item))
            {   
                $filters[] = new TFilter($item, 'like', $data->$item);
            }
        }

        if (isset($data->nm_pessoa))
        {
            $filters[] = new TFilter('SELECT nm_pessoa FROM pessoa WHERE pessoa.id = setor.id_pessoa', 'like', "%{$data->nm_pessoa}%");
        } 
        if (isset($data->cidade))
        {
            $filters[] = new TFilter('SELECT nm_cidade FROM endereco WHERE enderecoo.id = setor.id_endereco', 'like', "%{$data->cidade}%");
        }

        // salva o filtro
        TSession::setValue('Setor_filter', $filters);
        // serve para armazenar caso seja preciso repopular o campo de busca
        TSession::setValue('Setor_search_data', $data);

        $param = array();
        $param['offset']     = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
 
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
            TTransaction::open('patrimonio');

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

    function onClear() 
    { 
        $this->form->clear( TRUE );
    }

    function onExportPDF($param)
    {
        try {
            // processa o Template e retorna o renderer
            $html = $this->onCheckStatus($param);
            
            // obtém o HTML como string
            $contents = $html->getContents();
            
            // converte o HTML em PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/setores.pdf';
            
            // escreve em disco
            file_put_contents($file, $dompdf->output());
            // exibe em janela
            $window = TWindow::create('Setor Status', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
}