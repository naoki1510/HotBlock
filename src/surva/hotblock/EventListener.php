<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:02
 */

namespace surva\hotblock;

use naoki1510\Team\TeamManager;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;

class EventListener implements Listener {
    
    public function __construct(HotBlock $hotBlock) {
        //$this->hotBlock = $hotBlock;
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        $world = $entity->getLevel();
        $block = $world->getBlock($entity->floor()->subtract(0, 1));

        if($entity instanceof Player
        && in_array($world->getName(), HotBlock::getinstance()->getConfig()->get("world", ['pvp']))
        && TeamManager::getinstance()->exists($entity)
        && $block->getDamage() === TeamManager::getinstance()->getTeamOf($entity)->getColor()['block']
        && $block->getId() === Item::fromString(HotBlock::getinstance()->getConfig()->get('safeblock', 'stained_glass'))->getId()) {
            $event->setCancelled();
        }
    }
    
    public function onPlayerAttack(EntityDamageByEntityEvent $event) {
        $damaged = $event->getEntity();
        $attacker = $event->getDamager();
        if($damaged instanceof Player && TeamManager::getinstance()->exists($damaged) && TeamManager::getinstance()->exists($attacker)){
            if(TeamManager::getinstance()->getTeamOf($damaged) === TeamManager::getinstance()->getTeamOf($attacker)){
                $event->setCancelled(true);
            }
        }
    }

    public function onPacketSend(DataPacketSendEvent $e)
    {
        //TeamManager::getinstance()->onDataPacketSend($e);
        if ($e->getPacket()->getName() === 'SetEntityDataPacket' || $e->getPacket()->getName() === 'AddPlayerPacket') {
			$targetplayer = $e->getPlayer();
			if (isset($e->getPacket()->metadata[4][1]) && isset($e->getPacket()->entityRuntimeId)){
				$sourceplayer = HotBlock::getinstance()->getServer()->findEntity($e->getPacket()->entityRuntimeId);
				
				if(
				!empty($sourceplayer)
					&&
				TeamManager::getinstance()->exists($sourceplayer)
					&&
				!TeamManager::getinstance()->getTeamOf($sourceplayer)->exists($targetplayer)) {
					
					if (isset($e->getPacket()->metadata[4][1])) {
						$e->getPacket()->metadata[4][1] = '';
					}

					if (isset($e->getPacket()->username)) {
						$e->getPacket()->username = '';
					}
					
					//var_dump($e->getPacket());
					//$player->sendDataPacket($e->getPacket());
				}
			
			}
		}
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        if(TeamManager::getinstance()->exists($event->getPlayer())){
            TeamManager::getinstance()->leave($event->getPlayer());
        }
    }

    /*
    public function onJoin(PlayerJoinEvent $event) {
    	
    }*/
}
