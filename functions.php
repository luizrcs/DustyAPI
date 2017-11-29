<?php
// funções para a API
require 'config.php';

class nameUtils {

  // Função que checa se a UUID inserida é mais velha que 3 minutos
  // e também faz mais um monte de coisa
  public function uuid2name($uuid){
    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);

    $stmt = $mysqli->prepare("SELECT * FROM `accounts` WHERE `uuid` = ?");
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){

      $uuid = str_replace('-',"",$uuid);
      $json = file_get_contents('https://sessionserver.mojang.com/session/minecraft/profile/'. $uuid, true);
      $json = json_decode($json, true);
      $nome = $json{'name'};
      $uuid = preg_replace("/^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$/", "$1-$2-$3-$4-$5", $uuid);

      $stmt = $mysqli->prepare("INSERT INTO `accounts` (uuid, username, last_update) VALUES (?, ?, ?)");
      $unix = time();
      $stmt->bind_param("ssi", $uuid, $nome, $unix);
      $stmt->execute();


    }elseif($result->num_rows == 1){
      while($dados = $result->fetch_assoc()){
        if($dados['last_update'] + 90 < time()){

          $uuid = str_replace('-',"",$uuid);
          $json = file_get_contents('https://sessionserver.mojang.com/session/minecraft/profile/'. $uuid, true);
          $json = json_decode($json, true);
          $nome = $json{'name'};
          $uuid = preg_replace("/^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$/", "$1-$2-$3-$4-$5", $uuid);


          $stmt = $mysqli->prepare("UPDATE `accounts` SET username= '" . $nome . "', last_update=" . time() . "  WHERE `uuid` = ?");
          $stmt->bind_param("s", $uuid);
          $stmt->execute();


        }else{
          $nome = $dados['username'];
        }
      }

    }

    return $nome;

  }

  // Função que converte nomes para uuids e faz mais um monte de coisa
  public function name2uuid($name){

    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);

    $stmt = $mysqli->prepare("SELECT * FROM `accounts` WHERE `username` = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){

      $json = file_get_contents('https://api.mojang.com/users/profiles/minecraft/'. $name, true);
      $json = json_decode($json, true);
      $uuid = $json{'id'};
      $uuid = preg_replace("/^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$/", "$1-$2-$3-$4-$5", $uuid);


      $stmt = $mysqli->prepare("INSERT INTO `accounts` (uuid, username, last_update) VALUES (?, ?, ?)");
      $unix = time();
      $stmt->bind_param("ssi", $uuid, $name, $unix);
      $stmt->execute();


    }elseif($result->num_rows == 1){
      while($dados = $result->fetch_assoc()){
        if($dados['last_update'] + 90 < time()){

          $json = file_get_contents('https://api.mojang.com/users/profiles/minecraft/'. $name, true);
          $json = json_decode($json, true);
          $uuid = $json{'id'};
          $uuid = preg_replace("/^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$/", "$1-$2-$3-$4-$5", $uuid);


          $stmt = $mysqli->prepare("UPDATE `accounts` SET uuid= ?, last_update= ? WHERE `username` = ?");
          $time = time();
          $stmt->bind_param("sis", $uuid, $time, $name);
          $stmt->execute();


        }else{
          $uuid = $dados['uuid'];
        }
      }

    }


    return $uuid;
  }


}


class DustyAPI extends nameUtils {

  // Função simples para retornar mensagens de retorno ao receber um request
  public function StatusRetorno($id){
      switch ($id) {
        case 1:
          $status = json_encode(array("status"=>1));
          break;
        case 2:
          $status = json_encode(array("status"=>2));
          break;
        case 3:
          $status = json_encode(array("status"=>3));
          break;
        default:
          $status = json_encode(array("status"=>0));
      }

      return $status;
  }


  // Função para checar se o IP da pessoa fazendo o request está na lista de IPs permitidos
  public function checarIP($IP){
    global $config;
    if (in_array($IP, $config["allowedIP"])) {
        return true;
    }
  }

  // Função para banir players. Favor usar -1 para bans permanentes.
  // Não esquecer da data do ban.
  public function banirPlayer($uuid, $punidor, $motivo, $tempo){
    // conexão com MySQL e etc. em prepared statement

    // Retorna "1" em um json para confirmar o ban.
    return $this->StatusRetorno(1);
  }



  // Função para desbanir players
  // Não esquecer da data
  public function desbanirPlayer($uuid){


    return $this->StatusRetorno(1);
  }



  // Função para mutar players.
  public function mutarPlayer($uuid, $punidor, $motivo, $tempo){

    return $this->StatusRetorno(1);
  }



  // Função para desmutar players
  public function desmutarPlayer($uuid){

    return $this->StatusRetorno(1);
  }

  // Função que retorna informações gerais sobre o jogador (ban, mute, etc.)
  public function statusPlayer($uuid){


  }


  // Função que retorna perfil do jogador (kills, deaths, money, etc.)
  public function perfilPlayer($uuid){
    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    $stmt = $mysqli->prepare("SELECT * FROM `perfil` WHERE `uuid` = ?");
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){
      return $this->StatusRetorno(2);
    }else{
      return json_encode($result->fetch_assoc());
    }

  }


  // Função para salvar o perfil de jogadores.
  // Não esquecer de usar foreach, pois será recebido uma array json que pode conter mais de 1 jogadores
  public function salvarPerfil($data){
    // $data é uma array json com uuid do jogador e kills, deaths e etc.
    global $config;
    $data = json_decode($data, true);
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    foreach ($data as $player) {
      $stmt = $mysqli->prepare("SELECT * FROM `perfil` WHERE `uuid` = ?");
      $stmt->bind_param("s", $player['uuid']);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows == 0){
        $stmt = $mysqli->prepare("INSERT INTO `perfil` (uuid, kills, deaths, killStreak, maxKillStreak, xp, money, hgWins, hgLosses, oneVsOneWins, oneVsOneLosses) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiiddii", $player['uuid'], $player['kills'], $player['deaths'], $player['killStreak'], $player['maxKillStreak'], $player['xp'], $player['money'], $player['hgWins'], $player['hgLosses']);
        $stmt->execute();

      }else{
        $stmt = $mysqli->prepare("UPDATE `perfil` SET kills=?, deaths=?, killStreak=?, maxKillStreak=?, xp=?, money=?, hgWins=?, hgLosses=?, oneVsOneWins=?, oneVsOneLosses=? WHERE `uuid` = ?");
        $stmt->bind_param("iiiiddiis", $player['kills'], $player['deaths'], $player['killStreak'], $player['maxKillStreak'], $player['xp'], $player['money'], $player['hgWins'], $player['hgLosses'], $player['oneVsOneWins'], $player['oneVsOneLosses'], $player['uuid']);
        $stmt->execute();
      }

    }

    if($stmt->affected_rows == 0){
      return $this->StatusRetorno(3);
    }else{
      return $this->StatusRetorno(1);
    }
  }

  // Função para retornar os top jogdadores dado um filtro especíco
  // E um $max para saber quantos players deve retornar e $ordem para DESCendente ou ASCendente
  public function getLeaderboard($type, $max, $ordem){
    global $config;

      // Cache básico baseado em arquivos
      $cache = $type . "_" . $ordem . "_" . $max . ".json";
      if (file_exists($cache) && (filemtime($cache) > (time() - 60 * 10))) {
          $leaderboard = file_get_contents($cache);

      }else{

        $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
        $stmt = $mysqli->prepare("SELECT uuid," . $type . " FROM `perfil` ORDER BY " . $type . " " . $ordem . " LIMIT ?");
        $stmt->bind_param("i", $max);
        $stmt->execute();
        $result = $stmt->get_result();


          $array = array();
          while($dados = $result->fetch_assoc()){
          $array[] = $dados;
          }

          //essa parte do código circula pelas uuids e converte cada uma para nome legível
          foreach ($array as $key => $player) {
              $playername                  = $this->uuid2name($player['uuid']);
              $array[$key]['uuid']         = $playername;
          }

        $leaderboard_json = json_encode($array);
        $fh = fopen($cache, 'w+') or die('Erro ao salvar o cache do leaderboard. Se o problema persistir, comunique um administrador');
        fwrite($fh, $leaderboard_json);
        fclose($fh);
        $leaderboard = file_get_contents($cache);

    }
    return $leaderboard;


  }




}

?>
