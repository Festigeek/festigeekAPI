<?php
/*
 * Exception bidon visant à isoler le travail un peu baclé fait pour les inscriptions à la LAN 2017.
 * Si cette Exception est levée, cela signifie exclusivement qu'il s'agit d'un bout de code ajouté
 * en avril 2017.
 */

namespace App\Exceptions;

use Exception;

class SchlagException extends Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'Code de Schlag a merdé...';
}