<?php

namespace naoki1510\Tasks;


use naoki1510\Game\GameManager;
use naoki1510\Game\TeamManager;
use pocketmine\block\Block;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use surva\hotblock\HotBlock;


class GameTask extends Task{
    /** @var HotBlock */
    //private $hotBlock;

    /** @var TeamManager */
    //private $teamManager;

    public function __construct(HotBlock $hotBlock) {
        //$this->hotBlock = $hotBlock;
        //$this->teamManager = $hotBlock->getTeamManager();
        //$this->config = 
    }

    public function onRun(int $currentTick) {
        $gameLevel = GameManager::getInstance()->getGameLevel();

        //$playersOnBlock = [];
        $teamsOnBlock = 0;

        foreach ($gameLevel->getPlayers() as $player) {
            $blockUnderPlayer = $gameLevel->getBlock($player->subtract(0, 0.5));

            if($blockUnderPlayer->getId() === Item::fromString(HotBlock::getinstance()->getConfig()->get('areablock', Block::WOOL))->getId()){
                    if (count($gameLevel->getPlayers()) < HotBlock::getinstance()->getConfig()->get("players", 2)) {
                        //GameManager::getInctance()->getConfig()->get('point', 1)
                        $player->sendTip(
                            HotBlock::Translate(
                                "block.lessplayers",
                                array("count" => HotBlock::getinstance()->getConfig()->get("players", 2))
                            )
                        );
                        return;
                    } else {
                        if (TeamManager::getInstance()->exists($player)) {
                            //$playersOnBlock += [$player];
                            $playerTeam = TeamManager::getInstance()->getTeamOf($player);
                            $teamsOnBlock++;
                            if ($teamsOnBlock === 1) {
                                $onlyTeam = $playerTeam;
                            }
                        }
                    }
                    break;
            }
        }

        if ($teamsOnBlock === 1) {
            $point = GameManager::getInctance()->getConfig()->get('point', 1);
            $period = GameManager::getInctance()->getConfig()->get('gameperiod', 0.2);
            $onlyTeam->addPoint($point);
            
        }
        
        if ($currentTick % 20 == 0) {
            GameManager::getInstance()->addCount(1);
        }
    }


    public function endGame()
    {
        $points = [];
        $oneteam = true;
        foreach ($this->getTeamManager()->getAllTeam() as $team) {
            if (empty($points[$team->getPoint()])) {
                $points[$team->getPoint()] = $team;
            } else {
                $oneteam = false;
            }
        }

        krsort($points);
        $winteam = array_shift($points);

        if ($oneteam) {
            foreach ($this->getTeamManager()->getAllPlayers() as $player) {
                if ($winteam->exists($player)) {
                    $player->addTitle('§cYou win!!', '§6Congratulations!', 2, 36, 2);
                    $this->getHotBlock()->getEconomy()->addMoney($player, $this->getHotBlock()->getConfig()->get('winmoney', 1000), false, "HotBlock");
                }else{
                    $player->addTitle('§9You Lose...', '§6Let\'s win next time', 2, 36, 2);
                }

                $items = [
                    Item::get(Item::FIREWORKS, 1, 64),
                    Item::get(Item::FIREWORKS, 2, 64),
                    Item::get(Item::FIREWORKS, 3, 64),
                    Item::get(Item::FIREWORKS, 4, 64),
                    Item::get(Item::FIREWORKS, 5, 64),
                    Item::get(Item::FIREWORKS, 6, 64),
                    Item::get(Item::FIREWORKS, 7, 64),
                    Item::get(Item::FIREWORKS, 8, 64),
                    Item::get(Item::FIREWORKS, 9, 64),
                        ];
            }
        }
    }

}