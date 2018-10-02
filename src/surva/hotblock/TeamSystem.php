<?php

/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:01
 */

namespace surva\hotblock;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;


class TeamSystem {
	/** @var HotBlock */
	private $hotBlock;

	public function __construct(HotBlock $hotBlock)
	{
		$this->hotBlock = $hotBlock;
	}

    public function sendNameTag($targetplayer, Player $sourceplayer, String $nametag) : void{
		if(!is_array($player)){
			$player = [$player];
		}

		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $sourceplayer->getId();
		$pk->metadata = $sourceplayer->propertyManager->getAll();
		$pk->metadata[Entity::DATA_NAMETAG][1] = $nametag;

		// Temporary fix for player custom name tags visible
		$includeNametag = isset($data[Entity::DATA_NAMETAG]);
		if(($isPlayer = $sourceplayer instanceof Player) and $includeNametag){
			$remove = new RemoveEntityPacket();
			$remove->entityUniqueId = $sourceplayer>getId();
			$add = new AddPlayerPacket();
			$add->uuid = $sourceplayer->getUniqueId();
			$add->username = $nametag;
			$add->entityRuntimeId = $sourceplayer->getId();
			$add->position = $sourceplayer->asVector3();
			$add->motion = $sourceplayer->getMotion();
			$add->yaw = $sourceplayer->yaw;
			$add->pitch = $sourceplayer->pitch;
			$add->item = $sourceplayer->getInventory()->getItemInHand();
			$add->metadata = $sourceplayer->propertyManager->getAll();
		}

		foreach($player as $p){
			if($p === $sourceplayer){
				continue;
			}
			$p->sendDataPacket(clone $pk);

			// will remove soon
			if($isPlayer and $includeNametag){
				$p->sendDataPacket(clone $remove);
				$p->sendDataPacket(clone $add);
			}
		}
	}
}