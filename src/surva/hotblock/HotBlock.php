<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:01
 */

namespace surva\hotblock;

use naoki1510\Commands\pvpCommand;
use naoki1510\Game\GameManager;
use naoki1510\Game\GameTask;
use naoki1510\Team\TeamManager;
use onebone\economyapi\EconomyAPI;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use raklib\protocol\Packet;
use surva\hotblock\tasks\GameResetTask;
use surva\hotblock\tasks\PlayerBlockCheckTask;
use surva\hotblock\tasks\PlayerCoinGiveTask;

class HotBlock extends PluginBase {
    /** @var HotBlock */
    private static $instance;

    /**
     * @return HotBlock
     */
    public static function getInstance() : HotBlock
    {
        return self::$instance;
    }

    /** @var Config */
    private $messages;

    /** @var EconomyAPI */
    private $economy;

    /** @var TeamManager */
    private $teamManager;

    /** @var GameManager */
    private $gameManager;

    public function onEnable() {
        self::$instance = $this;
        
        $this->gameManager = new GameManager($this);
        $this->teamManager = new TeamManager($this);
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->saveDefaultConfig();

        $this->messages = new Config(
            $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->getServer()->getPluginManager()->registerEvents($this->teamManager, $this);
        $this->getServer()->getCommandMap()->register('hotblock', new pvpCommand('pvp'));

        $this->getScheduler()->scheduleRepeatingTask(
            new PlayerBlockCheckTask($this),
            $this->getConfig()->get("checkspeed", 0.25) * 20
        );
        $this->getScheduler()->scheduleRepeatingTask(
            new PlayerCoinGiveTask($this),
            $this->getConfig()->get("coinspeed", 0.25) * 20
        );
    }

    /**
     * Get a translated message
     *
     * @param string $key
     * @param array $replaces
     * @return string
     */
    public function getMessage(string $key, array $replaces = array()): string {
        if($rawMessage = $this->getMessages()->getNested($key)) {
            if(is_array($replaces)) {
                foreach($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }
            return $rawMessage;
        }
        return $key;
    }

    public static function Translate(string $key, array $replaces = array()): string {
        if($rawMessage = HotBlock::getInstance()->getMessages()->getNested($key)) {
            if(is_array($replaces)) {
                foreach($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }
            return $rawMessage;
        }
        return $key;
    }

    /**
     * @return EconomyAPI
     */
    public function getEconomy(): EconomyAPI {
        return $this->economy;
    }
	/**
     * @return TeamManager
     */
    public function getTeamManager(): TeamManager {
        return $this->teamManager;
    }

    public function getGameManager() : GameManager {
        return $this->gameManager;
    }
    /**
     * @return Config
     */
    public function getMessages(): Config {
        return $this->messages;
    }
    public function getGameLevel() : Level
    {
        return $this->getGameManager()->getGameLevel();
    }
}
