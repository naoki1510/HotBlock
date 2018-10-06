<?php

namespace naoki1510\Team;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use surva\hotblock\HotBlock;
// use pocketmine\entity\Entity;

class Team {
	/** @var HotBlock */
	private $hotBlock;

	/** @var TeamManager */
	private $teamManager;
	
	/** @var String */
	public $name;
	public $textColor;
	
	/** @var int */
	public $points;
	public $blockColor;
	
	/** @var Player[] */
	private $players;
	
	/** @var Position */
	public $spawn;
	
	public function __construct(HotBlock $hotBlock, String $name, Array $color, Vector3 $pos = null) {
		$this->hotBlock = $hotBlock;
		//$this->teamManager = $hotBlock->getTeamManager();
		$this->name = $name;
		$this->textColor = $color['text'] ?? 'f';
		$this->blockColor = $color['block'] ?? '0';
		$this->players = [];
		$this->points = 0;
		$this->spawn = $pos ? ($pos instanceof Position ? $pos : Position::fromObject($pos, Server::getInstance()->getDefaultLevel())) : null;
	}
	
	public function add(Player $player) : bool {
		if (!$this->exists($player)) {
			$this->players[$player->getName()] = $player;
			$player->setNameTag('§' . $this->textColor . $player->getName());
			$player->sendMessage(HotBlock::Translate('team.join',['color' => $this->textColor, 'name' => $this->getName()]));
			$player->setAllowMovementCheats(true);
			$player->setSpawn($this->spawn ?? Server::getInstance()->getDefaultLevel()->getSpawnLocation());
			//$player->teleport($this->spawn ?? Server::getInstance()->getDefaultLevel()->getSpawnLocation());
			return true;
		}
		return false;
	}
	
	public function remove(Player $player) : bool {
		if ($this->exists($player)) {
			unset($this->players[$player->getName()]);
			$player->setNameTag($player->getName());
			$player->sendMessage(HotBlock::Translate('team.leave', ['color' => $this->textColor, 'name' => $this->getName()]));
			return true;
		}
		return false;
	}
	
	public function exists(Player $player) : bool {
		return isset($this->players[$player->getName()]);
	}

	public function addPoint(Int $point = 1){
		$this->points += $point;
	}

	public function getPoint() : Int {
		return $this->points;
	}

	public function setPoint(int $point){
		$this->points = $point;
	}
	
	public function setSpawn(Vector3 $pos) {
	    $this->spawn = $pos instanceof Position ? $pos : Position::fromObject($pos, Server::getInstance()->getDefaultLevel());
	}
	
	public function getSpawn() : Position{
	    return $this->spawn;
	}

	public function init()
	{
		$this->players = [];
		$this->points = 0;
	}
	
	/**
	 * @return Player[]
	 */
	public function getAllPlayers(){
		return $this->players;
	}

	public function teleportAll(Vector3 $pos){
		foreach ($this->getAllPlayers() as $player) {
			$player->teleport($pos);
		}
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		return $this->name = $name;
	}
	
	public function getColor() : array{
		return ['text' => $this->textColor, 'block' => $this->blockColor];
	}
	
	public function getPlayerCount() {
		return count($this->players);
	}
}