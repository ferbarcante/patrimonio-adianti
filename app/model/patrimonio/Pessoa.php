<?php

use Adianti\Database\TRecord;

class Pessoa extends TRecord 
{
    const TABLENAME = "pessoa";
    const PRIMARYKEY = "id_pessoa";
    const IDPOLICY = "serial";

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('nm_pessoa');
        parent::addAttribute('nu_cpf');
        parent::addAttribute('ds_email');
    }
}