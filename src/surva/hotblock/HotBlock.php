<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:01
 */

namespace surva\hotblock;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\CommandSender;
use surva\hotblock\tasks\PlayerCoinGiveTask;
use surva\hotblock\tasks\PlayerBlockCheckTask;
use raklib\protocol\Packet;
use pocketmine\event\server\DataPacketSendEvent;

class HotBlock extends PluginBase {
    /* @var Config */
    private $messages;

    /* @var EconomyAPI */
    private $economy;
    
    /** @var TeamManager */
    private $teammanager;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->teammanager = new TeamManager($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->messages = new Config(
            $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        

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
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    	switch ($command->getName()) {
    		case 'pvp': 
    			if ($sender instanceof Player) {
                    if(empty($this->getTeamManager()->getTeamOf($sender))){
                        $this->getTeamManager()->join($sender);
                    }else{
                        $this->getTeamManager()->leave($sender);
                    }
    				
    			}else {
    				$sender->sendMessage('you can use this in game');
    			}
    			return true;
    		break;
    	}
    	return false;
    }

    public function onPacketSend(DataPacketSendEvent $event){
        $this->getTeamManager()->onDataPacketSend($event);
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
        return $this->teammanager;
    }

    /**
     * @return Config
     */
    public function getMessages(): Config {
        return $this->messages;
    }
}
