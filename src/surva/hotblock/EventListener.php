<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:02
 */

namespace surva\hotblock;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class EventListener implements Listener {
    /* @var HotBlock */
    private $hotBlock;

    /** @var TeamManager */
    private $teamManager;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
        $this->teamManager = $hotBlock->getTeamManager();
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        $world = $entity->getLevel();
        $block = $world->getBlock($entity->floor()->subtract(0, 1));

        if($entity instanceof Player
        && in_array($world->getName(), $this->getHotBlock()->getConfig()->get("world", ['pvp']))
        && $this->getTeamManager()->exists($entity)
        && $block->getDamage() === $this->getTeamManager()->getTeamOf($entity)->getColor()['block']
        && $block->getId() === Item::fromString($this->getHotBlock()->getConfig()->get('safeblock', 'stained_glass'))->getId()) {
            $event->setCancelled();
        }
    }
    
    public function onPlayerAttack(EntityDamageByEntityEvent $event) {
        $damaged = $event->getEntity();
        $attacker = $event->getDamager();
        if($damaged instanceof Player && $this->getTeamManager()->exists($damaged) && $this->getTeamManager()->exists($attacker)){
            if($this->getTeamManager()->getTeamOf($damaged) === $this->getTeamManager()->getTeamOf($attacker)){
                $event->setCancelled(true);
            }
        }
    }

    public function onPacketSend(DataPacketSendEvent $e)
    {
        //$this->getTeamManager()->onDataPacketSend($e);
        if ($e->getPacket()->getName() === 'SetEntityDataPacket' || $e->getPacket()->getName() === 'AddPlayerPacket') {
			$targetplayer = $e->getPlayer();
			if (isset($e->getPacket()->metadata[4][1]) && isset($e->getPacket()->entityRuntimeId)){
				$sourceplayer = $this->getHotBlock()->getServer()->findEntity($e->getPacket()->entityRuntimeId);
				
				if(
				!empty($sourceplayer)
					&&
				$this->getTeamManager()->exists($sourceplayer)
					&&
				!$this->getTeamManager()->getTeamOf($sourceplayer)->exists($targetplayer)) {
					
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
        if($this->getTeamManager()->exists($event->getPlayer())){
            $this->getTeamManager()->leave($event->getPlayer());
        }
    }

    /*
    public function onJoin(PlayerJoinEvent $event) {
    	
    }*/

    /**
     * @return TeamManager
     */
    public function getTeamManager() : TeamManager{
        return $this->teamManager;
    }

    /**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}
