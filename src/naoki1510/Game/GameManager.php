<?php
namespace naoki1510\Game;

use naoki1510\Tasks\GameTask;
use naoki1510\Tasks\SendMessageTask;
use naoki1510\Team\TeamManager;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use surva\hotblock\HotBlock;

class GameManager
{
    /** @var GameManager */
    private static $instance;

    /** @return GameManager */
    public static function getInstance() : GameManager{
        return self::$instance;
    }

    /** @var HotBlock */
    private $hotBlock;

    /** @var TeamManager */
    private $teamManager;

    /** @var Config */
    public $gameConfig;

    /** @var bool */
    public $running;

    /** @var Level */
    public $gameLevel;
    
    /** @var Int */
    public $gameCount;


    public function __construct(HotBlock $hotBlock)
    {
        self::$instance = $this;

        $hotBlock->saveResource('gameConfig.yml');
        $this->gameConfig = new Config($hotBlock->getDataFolder() . 'gameConfig.yml', Config::YAML);

        $levelnames = $this->getGameConfig()->get("worlds", ['pvp']);
        $level = Server::getInstance()->getLevelByName($levelnames[array_rand($levelnames)]);
        $this->gameLevel = $level;

        $hotBlock->getScheduler()->scheduleRepeatingTask(new GameTask($hotBlock), 20);
        $hotBlock->getScheduler()->scheduleRepeatingTask(new SendMessageTask($hotBlock), 20);


    }

    public function startGame(){
        $this->running = true;
        foreach (TeamManager::getInstance()->getAllTeam() as $team) {
            $team->teleportAll($team->getSpawn());
        }

        //TeamManager::getInstance()->init();
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
                    $this->getHotBlock()->getEconomy()->addMoney($player, HotBlock::getInstance()->getConfig()->get('winmoney', 1000), false, "HotBlock");
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
                foreach ($items as $item) {
                    $player->getInventory()->addItem($item);
                }
            }
        }
    }

    public function isRunning(){
        return $this->running;
    }
    
    public function addCount(Int $cont = 1){
        $this->gameCount += $count;
    }
    
    public function getCount() {
        return $this->gameCount;
    }


    public function getGameLevel() : Level
    {
        return $this->gameLevel;
    }
    
    
    public function setGameLevel(Level $level)
    {
        $this->gameLevel = $level;
    }

    public function getGameConfig() : Config
    {
        return $this->gameConfig;
    }
}
