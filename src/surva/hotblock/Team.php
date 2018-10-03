<?php

namespace surva\hotblock;

use pocketmine\Player;
// use pocketmine\entity\Entity;

class Team {
	/** @var HotBlock */
	private $hotBlock;
	
	/** @var String */
	public $name;
	
	/** @var Int */
	private $color;
	
	/** @var Player[] */
	private $players;
	
	public function __construct(HotBlock $hotBlock, String $name, Int $color) {
		$this->hotBlock = $hotBlock;
		$this->name = $name;
		$this->color = $color;
	}
	
	public function add(Player $player) : bool {
		if (!$this->exists($player)) {
			$this->players[$player->getName()] = $player;
			$player->sendMessage('You are now belonging to' . $this->getName() . ' team.');
			return true;
		}
		return false;
	}
	
	public function remove(Player $player) : bool {
		if ($this->exists($player)) {
			unset($this->players[$player->getName()]);
			$player->sendMessage('You left ' . this->getName() .' team.');
			return true;
		}
		return false;
	}
	
	public function exists(Player $player) : bool {
		return isset($this->players[$player->getName()]);
	}
	
	public function getAllPlayers() : Player[] {
		return $this->players;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName() {
		return $this->name;
	}
	
	public function getColor() {
		return $this->color;
	}
	
	public function getPlayerCount() {
		return count($this->players);
	}
	
	/**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}