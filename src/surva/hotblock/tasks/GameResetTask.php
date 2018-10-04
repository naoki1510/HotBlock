<?php

namespace surva\hotblock\tasks;

use pocketmine\item\Item;
use pocketmine\block\Block;
use surva\hotblock\HotBlock;
use pocketmine\entity\Effect;
use pocketmine\scheduler\Task;
use surva\hotblock\TeamManager;
use pocketmine\entity\EffectInstance;

// This is the task to check players on block when game ended
class GameResetTask extends Task {
    /* @var HotBlock */
    private $hotBlock;

    /** @var TeamManager */
    private $teamManager;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
        $this->teamManager = $hotBlock->getTeamManager();
    }

    public function onRun(int $currentTick)
    {
        //foreach ($this->getHotBlock()->getConfig()->get("world", ['pvp']) as $levelname) {
            //if (!($gameLevel = $this->getHotBlock()->getServer()->getLevelByName($levelname))) {
            //    return;
            //}

            $points = [];
            $oneteam = true;
            foreach ($this->getTeamManager()->getAllTeam() as $team) {
                if(empty($points[$team->getPoint()])){
                    $points[$team->getPoint()] = $team;
                }else{
                    $oneteam = false;
                }
            }

            krsort($points);
            $winner = array_shift($points);

            if($oneteam){
                foreach ($this->getTeamManager()->getAllPlayers() as $player) {
                    $player->sendMessage('You win!!');
                    $this->getHotBlock()->getEconomy()->addMoney($player, $this->getHotBlock()->getConfig()->get('winmoney', 1000), false, "HotBlock");
                }
            }
        //}

        $this->getTeamManager()->init();
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
