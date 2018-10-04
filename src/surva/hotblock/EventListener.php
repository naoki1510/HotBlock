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
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;

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

        if($world->getName() === $this->getHotBlock()->getConfig()->get("world", "world")) {
            if($block->getId() === Block::PLANKS) {
                $event->setCancelled();
            }
        }
    }
    
    public function onPlayerAttack(EntityDamageByEntityEvent $event) {
        $damaged = $event->getEntity();
        $attacker = $event->getDamager();
        if($this->getTeamManager()->exists($damaged) && $this->getTeamManager()->exists($attacker)){
            if($this->getTeamManager()->getTeamOf($damaged)->getName() === $this->getTeamManager()->getTeamOf($attacker)){
                $event->setCancelled(true);
            }
        }
    }
    /*
    public function onJoin(PlayerJoinEvent $event) {
    	
    }*/

    /**
     * @return TeamManager
     */
    public function getTeamManager() : TeamManager
    {
        return $this->teammanager;
    }

    /**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}
