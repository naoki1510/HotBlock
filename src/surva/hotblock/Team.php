<?php

namespace surva\hotblock;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
// use pocketmine\entity\Entity;

class Team {
	/** @var HotBlock */
	private $hotBlock;

	/** @var TeamManager */
	private $teamManager;
	
	/** @var String */
	public $name;

	/** @var array */
	private $color;
	
	/** @var int */
	public $points;
	
	/** @var Player[] */
	private $players;
	
	/** @var Position */
	public $spawn;
	
	public function __construct(HotBlock $hotBlock, String $name, Array $color, Vector3 $pos = null) {
		$this->hotBlock = $hotBlock;
		//$this->teamManager = $hotBlock->getTeamManager();
		$this->name = $name;
		$this->color['text'] = $color['text'] ?? 'f';
		$this->color['block'] = $color['block'] ?? '0';
		$this->players = [];
		$this->points = 0;
		$this->spawn = $pos ? ($pos instanceof Position ? $pos : Position::fromObject($pos, Server::getInstance()->getDefaultLevel())) : null;
	}
	
	public function add(Player $player) : bool {
		if (!$this->exists($player)) {
			$this->players[$player->getName()] = $player;
			$player->setNameTag('§' . $this->color['text'] . $player->getName());
			$player->sendMessage('You are now belonging to §' . $this->color['text'] . $this->getName() . '§f team.');
			$player->setAllowMovementCheats(true);
			$player->setSpawn($this->spawn ?? Server::getInstance()->getDefaultLevel()->getSpawnLocation());
			return true;
		}
		return false;
	}
	
	public function remove(Player $player) : bool {
		if ($this->exists($player)) {
			unset($this->players[$player->getName()]);
			$player->setNameTag($player->getName());
			$player->sendMessage('You left §' . $this->color['text'] . $this->getName() . '§f team.');
			return true;
		}
		return false;
	}
	
	public function exists(Player $player) : bool {
		return isset($this->players[$player->getName()]);
	}

	public function addPoint(int $point = 1){
		$this->points += $point;
	}

	public function getPoint(){
		return $this->points;
	}

	public function setPoint(int $point){
		$this->points = $point;
	}
	
	public function setSpawn(Vector3 $pos) {
	    $this->spawn = $pos instanceof Position ? $pos : Position::fromObject($pos, $this->getServer()->getDefaultLevel());
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
	public function getAllPlayers() : array {
		return $this->players;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName() {
		return $this->name;
	}
	
	public function getColor() : array{
		return $this->color;
	}
	
	public function getPlayerCount() {
		return count($this->players);
	}
	

    /**
     * @return TeamManager
     */
    public function getTeamManager() : TeamManager{
        return $this->teamManager ?? ($this->teamManager = $this->getHotBlock()->getTeamManager());
    }

    /**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}