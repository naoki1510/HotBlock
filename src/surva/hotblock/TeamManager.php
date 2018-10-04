<?php

namespace surva\hotblock;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;


class TeamManager {
	/** @var HotBlock */
	private $hotBlock;
	
	/** @var Team[] */
	private $teams;
	private $players;

	public function __construct(HotBlock $hotBlock)
	{
		$this->hotBlock = $hotBlock;
		foreach ($this->hotBlock->getConfig()->get('teams', ['red' => ['block' => 14, 'text' => 'c'], 'blue' => ['block' => 11, 'text' => '9']]) as $name => $color) {
			if (!is_numeric($color['block'])) {
				$this->hotBlock->getLogger()->warning('The color of '.$name.' team is invalid.');
				$color['block'] = 0;
			}
			$this->teams[$name] = new Team($hotBlock, $name, $color);
		}
	}
	
	public function join(Player $player) : bool{
	    $minTeams = [];
	    $minPlayers = $this->getHotBlock()->getServer()->getMaxPlayers();
	    foreach ($this->teams as $team) {
	        if ($minPlayers > $team->getPlayerCount()) {
	            $minTeams = [$team];
	            $minPlayers = $team->getPlayerCount();
	        }elseif ($minPlayers == $team->getPlayerCount()) {
	        	array_push($minTeams, $team);
	        }
		}
		
		//var_dump($minTeams);
	    $addTeam = $minTeams[rand(0, count($minTeams) - 1)];
	    $this->players[$player->getName()] = $addTeam;
	    
		$enemy = [];
		foreach ($this->players as $playername => $team) {
		    if (!empty($this->getHotBlock()->getServer()->getPlayer($playername)) && $this->getTeamOf()) {
		        
		    }
		}
		
	    return $addTeam->add($player);
	    
	}

	public function leave(Player $player){
		$this->getTeamOf($player)->remove($player);
		unset($this->players[$player->getName()]);
	}

	public function exists(Player $player){
		return isset($this->players[$player->getName()]);
	}

	/**
	 * @return null|Team
	 */
	public function getTeamOf(Player $player) {
		return $this->players[$player->getName()] ?? null;
	}

	public function setTeamOf(Player $player, String $teamname) : bool{
		if(isset($this->teams[$teamname])){
			$this->teams[$teamname]->add($player);
			return true;
		}
		return false;
	}

    // This function is based on Entity::sendData()
    // ToDo: Check Packet
    public function sendNameTag($targetplayer, Player $sourceplayer, String $nametag) : void{
		if(!is_array($targetplayer)){
			$targetplayer = [$targetplayer];
		}

		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $sourceplayer->getId();
		$pk->metadata = $sourceplayer->propertyManager->getAll();
		$pk->metadata[Entity::DATA_NAMETAG] = [Entity::DATA_TYPE_STRING, $nametag];

		// Temporary fix for player custom name tags visible
		//$includeNametag = isset($data[Entity::DATA_NAMETAG]);
		
		$remove = new RemoveEntityPacket();
		$remove->entityUniqueId = $sourceplayer->getId();
		$add = new AddPlayerPacket();
		$add->uuid = $sourceplayer->getUniqueId();
		$add->username = $nametag;
		$add->entityRuntimeId = $sourceplayer->getId();
		$add->position = $sourceplayer->asVector3();
		$add->motion = $sourceplayer->getMotion();
		$add->yaw = $sourceplayer->yaw;
		$add->pitch = $sourceplayer->pitch;
		$add->item = $sourceplayer->getInventory()->getItemInHand();
		$add->metadata = $sourceplayer->getDataPropertyManager()->getAll();
		

		foreach($targetplayer as $p){
			if($p === $sourceplayer){
				continue;
			}
			$p->sendDataPacket(clone $pk);

			
			$p->sendDataPacket(clone $remove);
			$p->sendDataPacket(clone $add);
			
		}
	}

	public function onDataPacketSend(DataPacketSendEvent $e)
	{
		//$this->getLogger()->info($e->getPacket()->getName() . ' was Sended to ' . $e->getPlayer()->getName() . '.');
		if ($e->getPacket()->getName() === 'SetEntityDataPacket' || $e->getPacket()->getName() === 'AddPlayerPacket') {
			//var_dump($e->getPacket());
			$targetplayer = $e->getPlayer();
			if (isset($e->getPacket()->metadata[4])){
				$sourceplayer = $this->getHotBlock()->getServer()->getPlayer($e->getPacket()->metadata[4]);
				if(
				!empty($sourceplayer)
					&&
				!empty($this->getTeamOf($targetplayer))
					&&
				!empty($this->getTeamOf($sourceplayer))
					&&
				$this->getTeamOf($player)->getName() !== $this->getTeamOf($dataplayer)->getName()) {
					if (isset($e->getPacket()->metadata[4])) {
						$e->getPacket()->metadata[4][1] = '';
					}

					if (isset($e->getPacket()->username)) {
						$e->getPacket()->username = '';
					}

					//var_dump($e->getPacket()->metadata);
					//$player->sendDataPacket($e->getPacket());
				}
			
			}
		}
	}
	
	/**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}