<?php

namespace surva\hotblock;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;


class TeamManager {
	/** @var HotBlock */
	private $hotBlock;
	
	/** @var Team[] */
	private $teams
	private $players;

	public function __construct(HotBlock $hotBlock)
	{
		$this->hotBlock = $hotBlock;
		foreach ($this->hotBlock->getConfig->get('teams', ['red' => 0, 'blue' => 1]) as $name => $color) {
			if (!is_numeric($color)) {
				$this->hotBlock->getLogger()->warning('The color of '.$name.' team is invalid.');
				$color = 0;
			}
			$this->teams[$name] = new Team($hotBlock, $name, $color);
		}
	}
	
	public function join(Player $player) : bool{
	    $minTeams = []
	    $minPlayers = 0;
	    foreach ($this->teams as $team) {
	        if ($minPlayers > $team->getPlayerCount()) {
	            $minTeams = [$team];
	            $minPlayers = $team->getPlayerCount();
	        }elseif ($minPlayers == $team->getPlayerCount()) {
	        	array_push($minTeams, $team);
	        }
	    }
	    
	    $this->players[$player->getName()] = $addTeam->getName();
	    $addTeam = $minTeams[rand(0, count($minTeams))];
	    return $addTeam->add($player);
	    
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
	
	/**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}