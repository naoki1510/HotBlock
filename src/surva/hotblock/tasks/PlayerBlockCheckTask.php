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
use pocketmine\entity\EffectInstance;

class PlayerBlockCheckTask extends Task {
    /* @var HotBlock */
    private $hotBlock;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
    }

    public function onRun(int $currentTick) {
        //ワールドがないとき
        if(!($gameLevel = $this->getHotBlock()->getServer()->getLevelByName(
            $this->getHotBlock()->getConfig()->get("world", "world")
        ))) {
            return;
        }

        foreach($gameLevel->getPlayers() as $playerInLevel) {
            $blockUnderPlayer = $gameLevel->getBlock($playerInLevel->subtract(0, 0.5));

            switch($blockUnderPlayer->getId()) {
                case Item::fromString($this->getHotBlock()->getConfig()->get('safeblock', 'PLANKS'))->getId():
                    $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.safe"));
                    break;
                case Item::fromString($this->getHotBlock()->getConfig()->get('normalblock', 'END_STONE'))->getId():
                    $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.run"));
                    break;
                case Item::fromString($this->getHotBlock()->getConfig()->get('poisonblock', ' NETHERRACK'))->getId():
                    $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.poisoned"));

                    $effect = Effect::getEffectByName($this->getHotBlock()->getConfig()->get("effecttype", "POISON"));
                    $duration = $this->getHotBlock()->getConfig()->get("effectduration", 3) * 20;

                    $playerInLevel->addEffect(new EffectInstance($effect, $duration));
                    break;
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
