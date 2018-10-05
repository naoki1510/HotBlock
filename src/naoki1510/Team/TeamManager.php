<?php

namespace naoki1510\Team;

use naoki1510\Game\GameManager;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\utils\Config;
use surva\hotblock\HotBlock;


class TeamManager implements Listener{
	/** @var TeamManager */
	private static $instance;

	/**
	 * @return TeamManager
	 */
	public static function getInstance() : TeamManager
	{
		return self::$instance;
	}
	
	/** @var Team[] */
	private $teams;
	private $playerTeam;

	/** @var Config */
	public $teamConfig;

	public function __construct(HotBlock $hotBlock)
	{
		self::$instance = $this;
		//$this->hotBlock = $hotBlock;

		$hotBlock->saveResource('teamConfig.yml');
		$this->teamConfig = new Config($hotBlock->getDataFolder() . 'teamConfig.yml', Config::YAML);
		foreach ($this->getTeamConfig()->getAll() as $name => $data) {
			$spawndata = $this->getTeamConfig()->getNested($name . '.respawns.' . GameManager::getInstance()->getGameLevel()->getName() . '.' . $name, 'not set');
			if(substr_count($spawndata, ',') == 3){
				list($x, $y, $z, $level) = explode(',', $spawndata);
				$respawn = new Position((Int)$x, (Int)$y, (Int)$z, Server::getInstance()->getLevelByName($level) ?? Server::getInstance()->getDefaultLevel());
			}else{
				$respawn = Server::getInstance()->getDefaultLevel()->getSpawnLocation();
			}
			$this->teams[$name] = new Team($hotBlock, $name, $data['color'] ?? ['text' => 0, 'block' => 0], $respawn);
		}
	}
	
	public function join(Player $player) : bool{
	    $minTeams = [];
	    $minPlayers = Server::getInstance()->getMaxPlayers();
	    foreach ($this->teams as $team) {
	        if ($minPlayers > $team->getPlayerCount()) {
	            $minTeams = [$team];
	            $minPlayers = $team->getPlayerCount();
	        }elseif ($minPlayers == $team->getPlayerCount()) {
	        	array_push($minTeams, $team);
	        }
		}
		
	    $addTeam = $minTeams[rand(0, count($minTeams) - 1)];
	    $this->playerTeam[$player->getName()] = $addTeam;
	    
		foreach ($this->playerTeam as $playername => $team) {
			$source = Server::getInstance()->getPlayer($playername);
			if (!empty($source) && !$addTeam->exists($source)) {
				$this->sendNameTag($player, $source, '');
		        
		    }
		}
		
	    return $addTeam->add($player);
	}

	public function leave(Player $player) : void{
		if(!$this->exists($player)) return;

		$this->getTeamOf($player)->remove($player);
		unset($this->playerTeam[$player->getName()]);
		$player->teleport(Server::getInstance()->getDefaultLevel()->getSpawnLocation());

		return;
	}

	public function exists(Player $player){
		return isset($this->playerTeam[$player->getName()]);
	}

	public function init(){
		foreach ($this->teams as $team) {
			$team->init();
		}
		$this->playerTeam = [];
	}

	/**
	 * @return null|Team
	 */
	public function getTeamOf(Player $player) {
		return $this->playerTeam[$player->getName()] ?? null;
	}

	public function setTeamOf(Player $player, String $teamName) : bool {
		if($this->existsTeam($teamName)){
			$this->teams[$teamName]->add($player);
			return true;
		}
		return false;
	}

	/** 
	 * @return null|Team
	 */
	public function getTeam(String $teamName) {
		if ($this->existsTeam($teamName)) {
			return $this->teams[$teamName];
		}
		
		return null;
	}

	public function existsTeam(string $teamName) : bool {
		return isset($this->teams[$teamName]);
	}

	public function setSpawn(Vector3 $pos, Team $team){
		
		if (GameManager::getInstance()->getGameLevel() === $sender->getLevel()) {
			$team->setSpawn($pos);
		}

		$pos = \implode(',', [$sender->x, $sender->y, $sender->z, $sender->level->getName()]);
		$this->getTeamConfig()->setNested($team->getName() . '.respawns.' . $sender->getLevel()->getName() . '.' . $args[1], $pos);
		$this->getTeamConfig()->save();
	}

	/** 
	 * @return Player[]
	 */
	public function getAllPlayers(){
		$players = [];
		foreach ($this->players as $playername => $team) {
			array_push($players, Server::getInstance()->getPlayer($playername));
		}

		return $players;
	}

	/**
	 * @return Team[]
	 */
	public function getAllTeam(){
		return $this->teams;
	}

	public function onEntityDamage(EntityDamageEvent $event) : void
	{
		$entity = $event->getEntity();
		$world = $entity->getLevel();
		$block = $world->getBlock($entity->floor()->subtract(0, 1));

		if ($entity instanceof Player
			&& in_array($world->getName(), GameManager::getInstance()->getGameConfig()->get("world", ['pvp']))
			&& $this->exists($entity)
			&& $block->getId() === Item::fromString(HotBlock::getinstance()->getConfig()->get('safeblock', 'stained_glass'))->getId()
			&& $block->getDamage() === $this->getTeamOf($entity)->getColor()['block']) {
			$event->setCancelled();
		}
	}

	public function onPlayerAttack(EntityDamageByEntityEvent $event)
	{
		$damaged = $event->getEntity();
		$attacker = $event->getDamager();
		if ($damaged instanceof Player && $this->exists($damaged) && $this->exists($attacker)) {
			if ($this->getTeamOf($damaged) === $this->getTeamOf($attacker)) {
				$event->setCancelled(true);
			}
		}
	}

	public function onPacketSend(DataPacketSendEvent $e)
	{
        //TeamManager::getinstance()->onDataPacketSend($e);
		if ($e->getPacket()->getName() === 'SetEntityDataPacket' || $e->getPacket()->getName() === 'AddPlayerPacket') {
			$targetplayer = $e->getPlayer();
			if (isset($e->getPacket()->metadata[4][1]) && isset($e->getPacket()->entityRuntimeId)) {
				$sourceplayer = HotBlock::getinstance()->getServer()->findEntity($e->getPacket()->entityRuntimeId);

				if (!empty($sourceplayer)
					&&
					$this->exists($sourceplayer)
					&&
					!$this->getTeamOf($sourceplayer)->exists($targetplayer)) {

					if (isset($e->getPacket()->metadata[4][1])) {
						$e->getPacket()->metadata[4][1] = '';
					}

					if (isset($e->getPacket()->username)) {
						$e->getPacket()->username = '';
					}
					
					//var_dump($e->getPacket());
					//$player->sendDataPacket($e->getPacket());
				}

			}
		}
	}

	public function onQuit(PlayerQuitEvent $event)
	{
		if ($this->exists($event->getPlayer())) {
			$this->leave($event->getPlayer());
		}
	}


    // This function is based on Entity::sendData()
    public function sendNameTag($targetplayer, Player $sourceplayer, String $nametag) : void{
		if(!is_array($targetplayer)){
			$targetplayer = [$targetplayer];
		}

		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $sourceplayer->getId();
		$pk->metadata[Entity::DATA_NAMETAG] = [Entity::DATA_TYPE_STRING, $nametag];
		
		$remove = new RemoveEntityPacket();
		$remove->entityUniqueId = $sourceplayer->getId();
		$add = new AddPlayerPacket();
		$add->uuid = $sourceplayer->getUniqueId();
		$add->username = $nametag;
		$add->entityRuntimeId = $sourceplayer->getId();
		$add->position = $sourceplayer->asVector3();
		$add->motion = $sourceplayer->getMotion();
		$add->yaw = $sourceplayer->yaw;
		$add->pitch = $sourceplayer->pitch;
		$add->item = $sourceplayer->getInventory()->getItemInHand();
		$add->metadata = $sourceplayer->getDataPropertyManager()->getAll();
		$add->metadata[Entity::DATA_NAMETAG] = [Entity::DATA_TYPE_STRING, $nametag];
		

		foreach($targetplayer as $p){
			if($p === $sourceplayer){
				continue;
			}
			$p->sendDataPacket(clone $pk);
			$p->sendDataPacket(clone $remove);
			$p->sendDataPacket(clone $add);
			
		}
	}
	
	public function getTeamConfig(): Config{
		return $this->teamConfig;
	}
}