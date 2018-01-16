<?php
require '../config.php';

class nameUtils {


}

class DustyAPI extends nameUtils {

  // função pra checar IP
  public function checarIP($IP){
    global $config;
    if (in_array($IP, $config["allowedIP"])) {
        return true;
    }
  }

  // função para retornar o perfil do jogador da uuidusty
  public function perfilPlayer($uuidusty){
    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    $stmt = $mysqli->prepare("SELECT * FROM `raid_perfil` WHERE `uuidusty` = ?");
    $stmt->bind_param("s", $uuidusty);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){
      return 2;
    }else{

      return json_encode($result->fetch_assoc());
    }
    $stmt->close();

  }

  // função para salvar o perfil do jogador
  public function salvarPerfil($data){
    // $data é uma array json com uuid do jogador e kills, deaths e etc.
    global $config;
    $data = json_decode($data, true);
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    foreach ($data as $player) {
      $stmt = $mysqli->prepare("SELECT * FROM `raid_perfil` WHERE `uuidusty` = ?");
      $stmt->bind_param("s", $player['uuid']);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows == 0){
        $stmt = $mysqli->prepare("INSERT INTO `raid_perfil` (uuidusty, kills, deaths, killstreak, maxKillStreak, money, clan) VALUE (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiids", $player['uuidusty'], $player['kills'], $player['deaths'], $player['killstreak'], $player['maxKillStreak'], $player['money'], $player['clan']);
        $stmt->execute();

      }else{
        $stmt = $mysqli->prepare("UPDATE `perfil` SET kills=?, deaths=?, killstreak=?, maxKillStreak=?, money=?, clan=? WHERE `uuidusty` = ?");
        $stmt->bind_param("iiiidss", $player['kills'], $player['deaths'], $player['killStreak'], $player['maxKillStreak'], $player['money'],  $player['clan'], $player['uuid']);
        $stmt->execute();
      }

    }

    if($stmt->affected_rows == 0){
      return 3;
    }else{
      return 1;
    }


  }

  // função para salvar/atualizar árvore
  public function salvarArvore($data){
    // $data é uma array json com as informações da árvore.
    global $config;
    $data = json_decode($data, true);
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    foreach ($data as $arvore) {
      $stmt = $mysqli->prepare("SELECT * FROM `raid_arvore` WHERE `uuid` = ?");
      $stmt->bind_param("s", $arvore['uuid']);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows == 0){
        $stmt = $mysqli->prepare("INSERT INTO `raid_arvore` (uuid, x, y, z, date) VALUE (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiii", $arvore['uuid'], $arvore['x'], $arvore['y'], $arvore['z'], $arvore['date']);
        $stmt->execute();

      }else{
        $stmt = $mysqli->prepare("UPDATE `raid_arvore` SET x, y, z, date WHERE `uuid` = ?");
        $stmt->bind_param("iiiis", $arvore['x'], $arvore['y'], $arvore['z'], $arvore['date'], $arvore['uuid']);
        $stmt->execute();
      }

    }

    if($stmt->affected_rows == 0){
      return 3;
    }else{
      return 1;
    }


  }

  // função para criar clans
  public function salvarClan($data){

  }

  // função para deletar clans
  public function delClan($data){

  }

  // função para pegar as warps de players
  public function getPlayerWarp($uuidusty){
    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    $stmt = $mysqli->prepare("SELECT * FROM `raid_players_warps` WHERE `uuidusty` = ?");
    $stmt->bind_param("s", $uuidusty);
    $stmt->execute();
    $result = $stmt->get_result();

    $warps = array();
    while($dados = $result->fetch_assoc()){
      $warps[] = $dados;
    }

    return json_encode($warps);

  }

  // função para pegar as warps de clans
  public function getTeamWarp($uuid){

    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    $stmt = $mysqli->prepare("SELECT * FROM `raid_teams_warps` WHERE `uuid` = ?");
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();

    $warps = array();
    while($dados = $result->fetch_assoc()){
      $warps[] = $dados;
    }

    return json_encode($warps);

  }

  // função que retorna informações da árvore especifica
  public function getArvore($uuid){
    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    $stmt = $mysqli->prepare("SELECT * FROM `raid_arvore` WHERE `uuid` = ?");
    $stmt->bind_param("s", $uuid);
    $stmt->execute();
    $result = $stmt->get_result();

    $arvore = array();
    while($dados = $result->fetch_assoc()){
      $arvore[] = $dados;
    }

    return json_encode($arvore);

  }

  // função que retorna todas as árvores
  public function getArvores(){
    global $config;
    $mysqli = new mysqli($config['database']['ip'], $config['database']['user'], $config['database']['password'], $config['database']['dbname']);
    $stmt = $mysqli->prepare("SELECT * FROM `raid_arvore`");
    $stmt->execute();
    $result = $stmt->get_result();

    $arvore = array();
    while($dados = $result->fetch_assoc()){
      $arvore[] = $dados;
    }

    return json_encode($arvore);

  }

  // função para criar/atualizar warp de players
  public function addWarpPlayer($data){

  }

  // função para deletar warps de player
  public function delWarpPlayer($data){

  }

  // função para criar/atualizar warp de times
  public function addWarpTeam($data){

  }

  // função pra deletar warp de times
  public function delWarpTeam($data){
    
  }



}

?>
