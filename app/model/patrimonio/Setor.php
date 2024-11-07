<?php

use Adianti\Database\TRecord;

class Setor extends TRecord
{
    const TABLENAME = "setor";
    const PRIMARYKEY = "id_setor";
    const IDPOLICY = "serial";

    private $pessoa;
    private $endereco;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('nm_setor');
        parent::addAttribute('cd_setor');
        parent::addAttribute('id_endereco');
        parent::addAttribute('id_pessoa');
    }

    public function set_pessoa(Pessoa $object)
    {
        $this->pessoa = $object;
        $this->id_pessoa = $object->id_pessoa;
    }
    
    public function get_pessoa()
    {
        // loads the associated object
        if (empty($this->pessoa))
            $this->pessoa = new Pessoa($this->id_pessoa);
    
        // returns the associated object
        return $this->pessoa;
    }

    public function get_endereco()
    {
        // loads the associated object
        if (empty($this->endereco))
            $this->endereco = new Endereco($this->id_endereco);
    
        // returns the associated object
        return $this->endereco;
    }
}