<?php

use Adianti\Database\TRecord;

class Endereco extends TRecord 
{
    const TABLENAME = "endereco";
    const PRIMARYKEY = "id_endereco";
    const IDPOLICY = "serial";

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('nu_cep');
        parent::addAttribute('nm_logradouro');
        parent::addAttribute('nm_bairro');
        parent::addAttribute('sg_uf');
        parent::addAttribute('ds_complemento');
    }
}