<?php
/**
 * Created by PhpStorm.
 * User: jarne
 * Date: 01.04.18
 * Time: 21:26
 */

namespace surva\hotblock\tasks;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\block\Block;
use surva\hotblock\HotBlock;
use pocketmine\scheduler\Task;
use surva\hotblock\TeamManager;

class PlayerCoinGiveTask extends Task {
    /* @var HotBlock */
    private $hotBlock;

    /** @var TeamManager */
    private $teamManager;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
        $this->teamManager = $hotBlock->getTeamManager();
    }

    public function onRun(int $currentTick) {
        foreach ($this->getHotBlock()->getConfig()->get("world", ['pvp']) as $levelname) {
            if (!($gameLevel = $this->getHotBlock()->getServer()->getLevelByName($levelname))) {
                return;
            }

            $playersOnBlock = [];
            $teamsOnBlock = 0;

            foreach ($gameLevel->getPlayers() as $playerInLevel) {
                $blockUnderPlayer = $gameLevel->getBlock($playerInLevel->subtract(0, 0.5));

                if ($blockUnderPlayer->getId() === Item::fromString($this->getHotBlock()->getConfig()->get('areablock', Block::WOOL))->getId()) {
                    if (count($gameLevel->getPlayers()) < $this->getHotBlock()->getConfig()->get("players", 2)) {
                        $playerInLevel->sendTip(
                            $this->getHotBlock()->getMessage(
                                "block.lessplayers",
                                array("count" => $this->getHotBlock()->getConfig()->get("players", 2))
                            )
                        );
                        return;
                    } else {
                        if ($this->getTeamManager()->exists($playerInLevel)) {
                            $playersOnBlock += [$playerInLevel];
                            $playerTeam = $this->getTeamManager()->getTeamOf($playerInLevel);
                            $teamsOnBlock++;
                            if ($teamsOnBlock === 1) {
                                $onlyTeam = $playerTeam;
                            }
                        }
                    }
                }
            }
            /** @var Team $onlyteam */
            if ($teamsOnBlock === 1) {
                $onlyTeam->addPoint($this->getHotBlock()->getConfig()->get('point', 1));
                foreach ($onlyTeam->getAllPlayers() as $player) {
                //$player->sendTip($this->getHotBlock()->getMessage("block.move"));
                    /** @var Player $player*/
                    $player->sendTip("§f\n§f\n".
                        $this->getHotBlock()->getMessage(
                            "block.coins",
                            array("count" => $this->getHotBlock()->getEconomy()->myMoney($player))
                        )
                    );
                }

                foreach ($playersOnBlock as $player) {
                    if($onlyTeam->exists($player)){
                        $this->getHotBlock()->getEconomy()->addMoney($player, 10, false, "HotBlock");
                    }
                }
            }
                // Make a message with points
            $message = '';
            foreach ($this->getTeamManager()->getAllTeam() as $team) {
                $message .= '§l§' . $team->getColor()['text'] . $team->getName() . ' Team§f:§' . $team->getColor()['text'] . $team->getPoint() . '§f,';
            }

            $message = trim($message, ",") . "\n§f\n§f";
            foreach ($gameLevel->getPlayers() as $player) {
                $player->sendPopup($message);
                $count = $this->getHotBlock()->getConfig()->get('gameduration', 180) - ($currentTick / 20) % $this->getHotBlock()->getConfig()->get('gameduration', 180);
                $player->setXpLevel($count);
                $player->setXpProgress($count / $this->getHotBlock()->getConfig()->get('gameduration', 180));
                if($count < 6 && $this->getTeamManager()->exists($player)) {
                    $player->addTitle('§6' . $count, '', 2, 16, 2);
                }

                //$player->addActionBarMessage("メッセージ");
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
