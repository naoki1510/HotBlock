<?php
/**
 * Created by PhpStorm.
 * User: jarne
 * Date: 01.04.18
 * Time: 21:18
 */

namespace surva\hotblock\tasks;

use pocketmine\item\Item;
use pocketmine\block\Block;
use surva\hotblock\HotBlock;
use pocketmine\entity\Effect;
use pocketmine\scheduler\Task;
use surva\hotblock\TeamManager;
use pocketmine\entity\EffectInstance;

class PlayerBlockCheckTask extends Task {
    /* @var HotBlock */
    private $hotBlock;

    /** @var TeamManager */
    private $teamManager;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
        $this->teamManager = $hotBlock->getTeamManager();
    }

    public function onRun(int $currentTick) {
        //ワールドがないとき
        foreach ($this->getHotBlock()->getConfig()->get("world", ['pvp']) as $levelName) {
            if (!($gameLevel = $this->getHotBlock()->getServer()->getLevelByName($levelName))) {
                return;
            }

            foreach ($gameLevel->getPlayers() as $playerInLevel) {
                $blockUnderPlayer = $gameLevel->getBlock($playerInLevel->subtract(0, 0.5));

                switch ($blockUnderPlayer->getId()) {
                    case Item::fromString($this->getHotBlock()->getConfig()->get('safeblock', 'stained_glass'))->getId():
                        $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.safe"));
                        break;
                        /*  
                    case Item::fromString($this->getHotBlock()->getConfig()->get('normalblock', 'END_STONE'))->getId():
                        $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.run"));
                        break;
                    case Item::fromString($this->getHotBlock()->getConfig()->get('poisonblock', ' NETHERRACK'))->getId():
                        $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.poisoned"));

                        $effect = Effect::getEffectByName($this->getHotBlock()->getConfig()->get("effecttype", "POISON"));
                        $duration = $this->getHotBlock()->getConfig()->get("effectduration", 3) * 20;

                        $playerInLevel->addEffect(new EffectInstance($effect, $duration));
                        break;*/
                }
            }
        }
    }

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
