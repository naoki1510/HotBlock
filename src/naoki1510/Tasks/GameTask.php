<?php

namespace naoki1510\Tasks;

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use surva\hotblock\HotBlock;
use surva\hotblock\TeamManager;


class GameTask extends Task{
    /** @var HotBlock */
    private $hotBlock;

    /** @var TeamManager */
    private $teamManager;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
        //$this->teamManager = $hotBlock->getTeamManager();
        //$this->config = 
    }

    public function onRun(int $currentTick) {
        $gameLevel = $this->getHotBlock()->getGameLevel();

        $playersOnBlock = [];
        $teamsOnBlock = 0;

        foreach ($gameLevel->getPlayers() as $player) {
            $blockUnderPlayer = $gameLevel->getBlock($player->subtract(0, 0.5));

            switch ($blockUnderPlayer->getId()) {
                case Item::fromString($this->getHotBlock()->getConfig()->get('safeblock', 'stained_glass'))->getId():
                    if($this->getTeamManager()->exists($player)
                    && $blockUnderPlayer->getDamage() === $this->getTeamManager()->getTeamOf($player)->getColor()['block']){
                        $player->sendTip($this->getHotBlock()->getMessage("ground.safe"));
                    }
                    break;

                case Item::fromString($this->getHotBlock()->getConfig()->get('areablock', Block::WOOL))->getId():
                    if (count($gameLevel->getPlayers()) < $this->getHotBlock()->getConfig()->get("players", 2)) {
                        $player->sendTip(
                            $this->getHotBlock()->getMessage(
                                "block.lessplayers",
                                array("count" => $this->getHotBlock()->getConfig()->get("players", 2))
                            )
                        );
                        return;
                    } else {
                        if ($this->getTeamManager()->exists($player)) {
                            $playersOnBlock += [$player];
                            $playerTeam = $this->getTeamManager()->getTeamOf($player);
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
            $onlyTeam->addPoint($this->getHotBlock()->getConfig()->get('point', 1));

            foreach ($playersOnBlock as $player) {
                if ($onlyTeam->exists($player)) {
                    $this->getHotBlock()->getEconomy()->addMoney($player, 10, false, "HotBlock");
                }
            }
        }
            // Make a message with points
        $message = '';
        foreach ($this->getTeamManager()->getAllTeam() as $team) {
            $message .= '§l§' . $team->getColor()['text'] . $team->getName() . ' Team§f:§' . $team->getColor()['text'] . $team->getPoint() . '§f,';
        }
        $message = trim($message, ",");
        foreach ($gameLevel->getPlayers() as $player) {
            $player->sendPopup($message);
            $countdown = $this->getHotBlock()->getConfig()->get('gameduration', 180) - ($currentTick / 20) % ($this->getHotBlock()->getConfig()->get('gameduration', 180) + $this->getHotBlock()->getConfig()->get('interval', 30));
            $player->setXpLevel($countdown);
            $player->setXpProgress($countdown / $this->getHotBlock()->getConfig()->get('gameduration', 180));

            if ($countdown < 6 && $this->getTeamManager()->exists($player)) {
                $player->addTitle('§6' . $countdown, '', 2, 16, 2);
            }

            if($countdown === $this->getHotBlock()->getConfig()->get('gameduration', 180)){
                $this->endGame();
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