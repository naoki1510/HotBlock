<?php

namespace naoki1510\Tasks;

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use surva\hotblock\HotBlock;
use naoki1510\Team\TeamManager;
use naoki1510\Game\GameManager;


class SendMessageTask extends Task
{
    public function __construct(HotBlock $hotBlock)
    {
        //$this->hotBlock = $hotBlock;
        //$this->teamManager = $hotBlock->getTeamManager();
    }

    public function onRun(int $currentTick)
    {
        $gameLevel = GameManager::getInstance()->getGameLevel();

        // Make a message with points
        $message = '';
        foreach (TeamManager::getInstance()->getAllTeam() as $team) {
            $message .= '§l§' . $team->getColor()['text'] . $team->getName() . ' Team§f:§' . $team->getColor()['text'] . $team->getPoint() . '§f,';
        }

        $message = trim($message, ",");
        
        foreach ($gameLevel->getPlayers() as $player) {
            $player->sendPopup($message);
            $gameduration = GameManager::getInstance()->getGameConfig()->get('gameduration', 180);
            //$interval = GameManager::getInstance()->getGameConfig()->get('interval', 30)
            //$countdown = $gameduration - ($currentTick / 20) % ($gameduration + $interval);
            $countdown = GameManager::getInstance()->getCount();
            $player->setXpLevel($countdown);
            $player->setXpProgress($countdown / ($duration));

            if ($countdown < 6 && TeamManager::getInstance()->exists($player)) {
                $player->addTitle('§6' . $countdown, '', 2, 16, 2);
            }
        }
    }

    public function endGame()
    {
        $points = [];
        $oneteam = true;
        foreach (TeamManager::getInstance()->getAllTeam() as $team) {
            if (empty($points[$team->getPoint()])) {
                $points[$team->getPoint()] = $team;
            } else {
                $oneteam = false;
            }
        }

        krsort($points);
        $winteam = array_shift($points);

        if ($oneteam) {
            foreach (TeamManager::getInstance()->getAllPlayers() as $player) {
                if ($winteam->exists($player)) {
                    $player->addTitle('§cYou win!!', '§6Congratulations!', 2, 36, 2);
                    HotBlock::getInstance()->getEconomy()->addMoney($player, HotBlock::getInstance()->getConfig()->get('winmoney', 1000), false, "HotBlock");
                } else {
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