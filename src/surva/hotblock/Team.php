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
	
	public function __construct(HotBlock $hotBlock, String $name, Array $color) {
		$this->hotBlock = $hotBlock;
		$this->name = $name;
		$this->color['text'] = $color['text'] ?? 'f';
		$this->color['block'] = $color['block'] ?? '0';
		$this->players = [];
	}
	
	public function add(Player $player) : bool {
		if (!$this->exists($player)) {
			$this->players[$player->getName()] = $player;
			$player->setNameTag('§' . $this->color['text'] . $player->getName());
			$player->sendMessage('You are now belonging to §' . $this->color['text'] . $this->getName() . '§f team.');
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
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}