<?php

/*
** "cliarg.php"
**
** $arg = [
**  'b'   => 'booleen', // Argument court => Argument long : 1 bool
**  'r:'  => '#',       // Argument court => -             : 1 arg
**  's+,' => 'size'     // Argument court => Argument long : n arguments séparés par des ','
** ];
** 
** Valeur de retour de la méthode Cliarg::Check(array $e) : 
** - -1 si le tableau d'arguments attendus contient une erreur
** - -2 s'il y a une erreur de la part de l'utilisateur
**
** =~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~=~
*/

define('USR_ERROR', -1);
define('ARG_ERROR', -2);

class Cliarg
{
  private $usrarg;
  private $cliarg;

  public function __construct(array $usrarg, array $cliarg)
  {
    $this->usrarg = $usrarg;
    array_shift($cliarg);
    $this->cliarg = $cliarg;
  }

  public function Check(array &$error)
  {
    $ret = $this->PrintUserError();
    if ($ret === USR_ERROR) return $ret;
    $values = [];
    $ret = $this->GetValues($error, $values);
    $this->SetBools($values);
    return ($ret === true) ? $values: $ret;
  }

  private function SetBools(&$values)
  {
    foreach ($this->usrarg as $short => $long)
    {
      $found = false;
      if (strlen($short) == 1) {
        foreach ($values as $arg => $val)
        {
          if (preg_match("#^$short$#", $arg)) {
            $found = true; 
            break;
          }
        }
        if (!$found) $values[$short] = false;
      }
    }
  }

  private function GetValues(array &$error, array &$values)
  {
    $not_found_arg = [];
    $val;
    $del = "";

    foreach ($this->cliarg as $arg)
    {
      $found_arg = false;
      foreach ($this->usrarg as $short => $long)
      {
        if (preg_match("#\+.$#", $short)) $del = preg_replace("#^.\+#", "", $short);
        $long = ($long === "#") ? "": "|(-$long)";
        $eq = !preg_match("#:|(\+.)$#", $short) ? "$": "=.+";
        $short = substr($short, 0, 1);

        if (preg_match("#^-($short$long)$eq#", $arg))
        {
          $found_arg = true;
          if (strpos($arg, "=")) {
            $val = preg_replace("#^[^=]+=#", "", $arg);
            if ($del !== "") $val = explode($del, $val);
          }
          else {
            $val = true;
          }
          $values[$short] = $val;
          break;
        }
      }
      if ($found_arg === false) $not_found_arg[] = $arg;
    }
    if (isset($not_found_arg[0])) {
      $error = $not_found_arg;
      return ARG_ERROR;
    }
    return true;
  }

  private function PrintUserError()
  {
    $e = false;

    foreach ($this->usrarg as $shortArg => $longArg)
    {
      if (!preg_match("#^[a-zA-Z](:|(\+.))?$#", $shortArg)) {
        $e = true;
        echo "\e[7;31mCaractère(s) illicite(s) argument court : '$shortArg'\n\e[0m";
      }
      if (!preg_match("#^(\#|[a-zA-Z]+)$#", $longArg))
      {
        $e = true;
        echo "\e[7;31mCaractère(s) illicite(s) argument long : '$longArg'\n\e[0m";
      }
    }
    if ($e) return USR_ERROR; 
    return true;
  }
}
