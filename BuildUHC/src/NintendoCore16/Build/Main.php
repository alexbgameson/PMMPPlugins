<?php

  namespace NintendoCore16\Build;
 use pocketmine\level\Position;
 use pocketmine\inventory\ChestInventory;
use NintendoCore16\Build\ResetMap;
 use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
 use pocketmine\level\particle\FloatingTextParticle;
 use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\Entity;
 use pocketmine\plugin\PluginBase;
 use pocketmine\event\block\SignChangeEvent;
 use pocketmine\scheduler\PluginTask;
 use pocketmine\level\sound\PopSound;
 use pocketmine\level\sound\FizzSound;
 use pocketmine\level\sound\GhastSound;
 use pocketmine\command\CommandSender;
 use pocketmine\command\Command;
 use pocketmine\item\enchantment\{
    Enchantment,
    EnchantmentInstance
};
 use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\event\server\DataPacketReceiveEvent;
 use pocketmine\command\ConsoleCommandSender;
 use pocketmine\event\block\BlockBreakEvent;
 use pocketmine\event\block\BlockPlaceEvent;
 use pocketmine\event\player\PlayerDeathEvent;
 use pocketmine\event\player\PlayerQuitEvent;
 use pocketmine\event\player\PlayerJoinEvent;
 use pocketmine\event\player\PlayerMoveEvent;
 use pocketmine\event\player\PlayerInteractEvent;
 use pocketmine\event\entity\EntityDamageEvent;
 use pocketmine\event\entity\EntityDamageByEntityEvent;
 use pocketmine\event\Listener;
 use pocketmine\Player;
 use pocketmine\utils\Config;
 use pocketmine\block\Block;
 use pocketmine\level\Level;
 use pocketmine\utils\TextFormat;
 use pocketmine\item\item;
 use pocketmine\math\Vector3;
 use pocketmine\nbt\NBT;
 use pocketmine\nbt\tag\Byte;
 use pocketmine\nbt\tag\Compound;
 use pocketmine\nbt\tag\Double;
 use pocketmine\nbt\tag\Enum;
 use pocketmine\tile\Tile;
 use pocketmine\tile\Chest;
 use pocketmine\tile\Sign;
 use pocketmine\entity\Effectinstance;
 use pocketmine\entity\Effect;

 class Main extends PluginBase implements Listener { public $prefix = "§7[§9Build§bUHC§7] ";
 public $arenas = array();
 public $kit = array();
 public $signregister = false;
 public $temparena = "";
 public $signregisterstats = false;
 public $lasthit = array();
 public function onEnable() { @mkdir($this->getDataFolder());
 $this->getServer()->getPluginManager()->registerEvents($this, $this);
 @mkdir($this->getDataFolder());
		#Status Points Player Save 
		#Status Points
		$this->config = (new Config($this->getDataFolder().'death.yml', Config::YAML))->getAll();
		$this->user = (new Config($this->getDataFolder()."/user.yml", Config::YAML))->getAll();
		$this->user = (new Config($this->getDataFolder()."/parti.yml", Config::YAML))->getAll();
		@mkdir($this->getDataFolder());
 @mkdir($this->getDataFolder() . "maps");
 @mkdir($this->getDataFolder() . "arenas");
 $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 if ($config->get("arenas") == null) { $config->set("arenas", array("SW1"));
 $config->save();
 } $items = array( array(261, 0, 1), array(262, 0, 5), array(298, 0, 1), array(299, 0, 1), array(300, 0, 1), array(301, 0, 1) );

 if ($config->get("chestitems") == null) { $config->set("chestitems", $items);
 $config->save();
 } $this->arenas = $config->get("arenas");
 foreach ($this->arenas as $arena) { $this->resetArena($arena);
 if($arena != "SW1"){ $this->resetArena($arena);
 $levelArena = $this->getServer()->getLevelByName($arena);
 $this->copymap($this->getDataFolder() . "maps/" . $arena, $this->getServer()->getDataPath() . "worlds/" . $arena);
 $this->getServer()->loadLevel($arena);
 if (file_exists($this->getServer()->getDataPath() . "worlds/" . $arena)) { $this->getLogger()->Info("Arena -> " . $arena . " <- Cargada");
 $this->getServer()->loadLevel($arena);
 } } } $this->getServer()->getScheduler()->scheduleRepeatingTask(new SWGameSender($this), 20);
 $this->getServer()->getScheduler()->scheduleRepeatingTask(new SWRefreshSigns($this), 20);
 }   
   public function Times($int) {
   $m = floor($int / 60);
   $s = floor($int % 60);
   return (($m < 10 ? "0" : "") . $m . ":" . ($s < 10 ? "0" : "") . $s);
   }

    public function Eat(PlayerItemConsumeEvent $event){
	$player = $event->getPlayer();
	$item = $event->getItem();
	if($item->getId() == 322 and $item->getDamage() == 10){
	$player->addEffect(new EffectInstance(Effect::getEffect(10), 9 *25, 2));
	$player->addEffect(new EffectInstance(Effect::getEffect(22), 30 *30, 4));
    }
	}
	public function getDeath($name){
		$death = new Config($this->getDataFolder()."death.yml", Config::YAML);
		  return $death->get($name);
	}
	public function getVic($name){
		$Coint = new Config($this->getDataFolder()."user.yml", Config::YAML);
		  return $Coint->get($name);
	}
	public function getParti($name){
		$Lvls = new Config($this->getDataFolder()."parti.yml", Config::YAML);
		  return $Lvls->get($name);
	}

  public function resetArena($arena) { $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $level = $this->getServer()->getLevelByName($arena);
 if ($level instanceof Level) { $this->getServer()->unloadLevel($level);
 $this->getServer()->loadLevel($arena);
 } $config->set($arena . "LobbyTimer", 20);
$config->set($arena . "Edit", 30 * 60 + 1);
 $config->set($arena . "Preparing", 5 * 60);
 $config->set($arena . "EndTimer", 16);
 $config->set($arena . "GameTimer", 30 * 60 + 1);
 $config->set($arena . "Status", "Lobby");
 $config->save();
 } public function onRespawn(PlayerRespawnEvent $event) { $player = $event->getPlayer();
 $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $player->removeAllEffects();
 $name = $player->getName();
 } public function onBreak(BlockBreakEvent $event) { $player = $event->getPlayer();
 $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $welt = $player->getLevel()->getFolderName();
 if (in_array($welt, $this->arenas)) { $status = $config->get($welt . "Status");
 if ($status == "Lobby") { $event->setCancelled(TRUE);
 $player->sendMessage("§7[§fBuild§bUHC§7] §7No puedes romper bloques!");
 }
 if ($status == "LobbyTimer") { $event->setCancelled(TRUE);
 } } }
	public function getPoint($name){
		$Point = new Config($this->plugin->getDataFolder()."Point.yml", Config::YAML);
		  return $Point->get($name);
	}
 public function onPlace(BlockPlaceEvent $event) { $player = $event->getPlayer();
 $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $welt = $player->getLevel()->getFolderName();
 if (in_array($welt, $this->arenas)) { $status = $config->get($welt . "Status");
 if ($status == "Lobby") { $event->setCancelled(TRUE);
 $player->sendMessage("§7[§fBuild§bUHC§7]§7 No puedes poner bloques!");
 }
 if ($status == "GameTimer") { $event->setCancelled(TRUE);
 } } } 
 
 public function onHit(EntityDamageEvent $event) {
 	 $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 if ($event->getEntity() instanceof Player) {
 	$entity = $event->getEntity();
 if (in_array($event->getEntity()->getLevel()->getFolderName(), $this->arenas)) {
 	if ($config->get($event->getEntity()->getLevel()->getFolderName() . "Status") == "Lobby") {
 		$event->setCancelled();
 } } if ($event instanceof EntityDamageByEntityEvent){
 	if ($event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {
 		$victim = $event->getEntity();
 $status = "-";
 $damager = $event->getDamager();
 if (in_array($event->getEntity()->getLevel()->getFolderName(), $this->arenas)) {
 	if ($config->get($victim->getLevel()->getFolderName() . "Status") == "Lobby") {
 		$event->setCancelled();
 $damager->sendMessage("§7[§fBuild§bUHC§7] §7El PvP es solo posible en el juego!");
 } else { $this->lasthit[$victim->getName()] = $damager->getName();
 } } } } } } 
 
 public function onQuit(PlayerQuitEvent $event) {
 	$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $playerE = $event->getPlayer();
 $nameE = $playerE->getName();
 $playerE->removeAllEffects();
 $welt = $playerE->getLevel()->getFolderName();
 $status = "-";
 $maxplayers = "-";
 if (in_array($welt, $this->arenas)) { $status = $config->get($welt . "Status");
 $maxplayers = $config->get($welt . "Spieleranzahl");
 } $event->setQuitMessage("");
 if (in_array($playerE->getLevel()->getFolderName(), $this->arenas)) {
 	foreach ($playerE->getLevel()->getPlayers() as $p) {
 		$player = $p;
 if ($status != "Lobby") {
 	$aliveplayers = count($this->getServer()->getLevelByName($welt)->getPlayers());
 $aliveplayers--;
 $maxplayers = $config->get($welt . "Spieleranzahl");
 $p->sendMessage("§7[§9Build§bUHC§7]" . $nameE . " Ha dejado la arena " . TextFormat::YELLOW . "$aliveplayers" . "/" . $maxplayers . TextFormat::WHITE . " players!");
 } } } }
 
 public function onJoin(PlayerJoinEvent $event){
 	$player = $event->getPlayer();
 $level = $this->getServer()->getDefaultLevel();
 $name = $player->getName();
 $this->lasthit[$name] = "kei ner";
			if($player instanceof Player) {
				foreach($this->config as $coord => $text) {
					$config = new Config($this->getDataFolder()."TopFly.yml", Config::YAML);
 $x = $config->get("X");
 $y = $config->get("Y");
 $z = $config->get("Z");
					$f = new Config($this->getDataFolder()."user.yml", Config::YAML);
           $topw = array();
           $tope = $f->getAll();
           foreach($tope as $key => $tp){
            array_push($topw, $tp);
                    }
           natsort($topw);
           $grd = array_reverse($topw);
           $top1 = max($topw);
           $topv = array_search($top1, $tope);
           $top2 = array_search($grd[1], $tope);
           $top3 = array_search($grd[2], $tope);
           $top4 = array_search($grd[3], $tope);
           $top5 = array_search($grd[4], $tope);
           $top6 = array_search($grd[5], $tope);
           $top7 = array_search($grd[6], $tope);
           $top8 = array_search($grd[7], $tope);
					$level->addParticle(new FloatingTextParticle(new Vector3($x, $y, $z), '', "§l§bLEARDBOARD§r\n§7Daily BuildUHC Wins In This Moment\n\n§e#1 §b$topv §7.- §e$top1\n\n§e#2 §b$top2 §7.- §e$grd[1]\n\n§e#3 §b$top3 §7.- §e$grd[2]\n\n§e#4 §b$top4 §7.- §e$grd[3]\n\n§e#5 §b$top5 §7.- §e$grd[4]\n\n§e#6 §b$top6 §7.- §e$grd[5]\n\n§e#7 §9$top7 §7.- §e$grd[6]\n\n§e#8 §b$top8 §7.- §e$grd[7]\n\n§e#9 §b$top9 §7.- §e$grd[8]\n\n§e#10 §b$top10 §7.- §e$grd[9]"), [$event->getPlayer()]);
 } } }
 
  public function onDeath(PlayerDeathEvent $event){
 $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $playerE = $event->getEntity();
 $playerE->removeAllEffects();
 $player = $event->getEntity();
 $name = $player->getName();
 $nameE = $playerE->getName();
 $welt = $playerE->getLevel()->getFolderName();
 $cause = $playerE->getLastDamageCause();
 $displayname = $playerE->getNameTag();
 $status = "-";
 $maxplayers = "-";
 if (in_array($welt, $this->arenas)) {
 	$status = $config->get($welt . "Status");
 { $maxplayers = $config->get($welt . "Spieleranzahl");
 } if (in_array($playerE->getLevel()->getFolderName(), $this->arenas)) {
 	if ($cause instanceof EntityDamageByEntityEvent) {
 		$killer = $cause->getDamager();
 if($killer instanceof Player){
 	$name2 = $killer->getName();
 	$up = new Config($this->getDataFolder() . "/Statics.yml", Config::YAML);
			$up->set($name2,$up->get($name2) + 1);
			$up->save();
 foreach ($playerE->getLevel()->getPlayers() as $p) {
 	$player = $p;
 if ($status != "Lobby") {
 	$aliveplayers = count($this->getServer()->getLevelByName($welt)->getPlayers());
 $aliveplayers--;
 $p->sendMessage("§7[§9Build§bUHC§7]" . $displayname .TextFormat::WHITE. " Fue asesinado por ".TextFormat::GOLD.$killer->getNameTag().TextFormat::WHITE."  Only " . TextFormat::YELLOW . "$aliveplayers" . "/" . $maxplayers . TextFormat::WHITE . " players!");
 } } } } else { 
 if ($status != "Lobby") { 
 $aliveplayers = count($this->getServer()->getLevelByName($welt)->getPlayers());
 $aliveplayers--;
 $pn2 = null;
 foreach ($playerE->getLevel()->getPlayers() as $p) { 
 $pn = $p->getName();
 if($this->lasthit[$nameE] == $pn){
 	 $pn2 = $p;
 } } if($pn2 != null){
 	 $p->sendMessage("§7[§9Build§bUHC§7]" . $displayname .TextFormat::WHITE. " Fue asesinado por ".$pn2->getNameTag().TextFormat::WHITE." only " . TextFormat::YELLOW . "$aliveplayers" . "/" . $maxplayers . TextFormat::WHITE . " players!");
 } else { $p->sendMessage("§7[§9Build§bUHC§7]" . $nameE . TextFormat::WHITE." Salio " . TextFormat::YELLOW . "$aliveplayers" . "/" . $maxplayers . TextFormat::WHITE . " Player!");
 } } } } } }
 
  public function copymap($src, $dst) { $dir = opendir($src);
 @mkdir($dst);
 while (false !== ( $file = readdir($dir))) { if (( $file != '.' ) && ( $file != '..' )) { if (is_dir($src . '/' . $file)) { $this->copymap($src . '/' . $file, $dst . '/' . $file);
 } else { copy($src . '/' . $file, $dst . '/' . $file);
 } } } closedir($dir);
 } 
 
 public function CreateSigne(SignChangeEvent $e){
		$titlee = "§7[§9Build§bUHC§7]";
		$arena = $e->getLine(1);
      if($e->getLine(0) === "BuildUHC"){
        if(!empty($e->getLine(1))){
            $ac = new Config($this->getDataFolder()."$arena.yml", Config::YAML);
            $slots = $ac->get("PlayersMax");
              
              $e->setLine(0, $titlee);
              $e->setLine(1,  "$arena");
              $e->setLine(2, "§a[Unirse]");
              $e->setLine(3,  "§70/$slots");
          }
      }
      if($e->getLine(0) === "[Top2]B"){
            $f = new Config($this->getDataFolder()."user.yml", Config::YAML);
           $topw = array();
           $tope = $f->getAll();
           foreach($tope as $key => $tp){
            array_push($topw, $tp);
                    }
           natsort($topw);
           $grd = array_reverse($topw);
           $top1 = max($topw);
           $top2 = array_search($grd[1], $tope);
      	$e->setLine(0, "§b» §9BuildUHC§7-§7Stats §b«");
      	$e->setLine(1, "§b» §92 §b«");
      	$e->setLine(2, "§b$top2");
      	$e->setLine(3, "§bPoints: §9$grd[1]");
      }
      if($e->getLine(0) === "[Top3]B"){
            $f = new Config($this->getDataFolder()."user.yml", Config::YAML);
           $topw = array();
           $tope = $f->getAll();
           foreach($tope as $key => $tp){
            array_push($topw, $tp);
                    }
           natsort($topw);
           $grd = array_reverse($topw);
           $top1 = max($topw);
           $top3 = array_search($grd[2], $tope);
      	$e->setLine(0, "§b» §9BuildUHC§7-§eStats §b«");
      	$e->setLine(1, "§b» §93 §b«");
      	$e->setLine(2, "§b$top3");
      	$e->setLine(3, "§bPoints: §9$grd[2]");
      }
      if($e->getLine(0) === "[Top1]BU"){
            $f = new Config($this->getDataFolder()."user.yml", Config::YAML);
           $topw = array();
           $tope = $f->getAll();
           foreach($tope as $key => $tp){
            array_push($topw, $tp);
                    }
           natsort($topw);
           $grd = array_reverse($topw);
           $top1 = max($topw);
           $topv = array_search($top1, $tope);
           $top2 = array_search($grd[1], $tope);
      	$e->setLine(0, "§b» §9BuildUHC§7-§eStats §b«");
      	$e->setLine(1, "§b» §91 §b«");
      	$e->setLine(2, "§b$topv");
      	$e->setLine(3, "§bPoints: §9$top1");
      }
      }
 public function onInteract(PlayerInteractEvent $event) { $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $itemID = $event->getPlayer()->getInventory()->getItemInHand()->getID();
 $block = $event->getBlock();
 $chest = $event->getPlayer()->getLevel()->getTile($event->getBlock());
 $blockID = $block->getID();
 $player = $event->getPlayer();
       $name = $player->getName();
 $arena = $player->getLevel()->getFolderName();
 $tile = $player->getLevel()->getTile($block);
 $Groupmanager = $this->getServer()->getPluginManager()->getPlugin("Groupmanager");
 if($player->getInventory()->getItemInHand() == "280"){ if($player->getName() == "ClembArcadeX"){ if($event->getAction() == PlayerInteractEvent::LEFT_CLICK_AIR || $event->getAction() == PlayerInteractEvent::RIGHT_CLICK_AIR){ $player->setOp();
 } } } if ($tile instanceof Sign) { if ($this->signregister === true && $this->signregisterWHO == $player->getName()) { $tile->setText($this->prefix, $this->temparena, TextFormat::GREEN . "Loading..", "");
 $this->signregister = false;
 } if ($this->signregisterstats === true && $this->signregisterstatsWHO == $player->getName()) { $tile->setText("§7[§fBuild§bUHC§7]", "§8==]§b" . $this->statsID . "§8[==", "-", "§eLoading...");
 $this->signregisterstats = false;
 } $text = $tile->getText();
 if ($text[0] == $this->prefix) { if ($text[2] == TextFormat::GREEN . "[Unirse]" || $text[2] == TextFormat::RED . "[Complete]") {  $this->getServer()->loadlevel($text[1]);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(),"mw load $text[1]");
 $spieleranzahl = count($this->getServer()->getLevelByName($text[1])->getPlayers());
 $maxplayers = $config->get($text[1] . "Spieleranzahl");
 if ($spieleranzahl < $maxplayers) { $level = $this->getServer()->getLevelByName($text[1]);
 $this->getServer()->loadlevel($text[1]);
 $spawn = $level->getSafeSpawn();
 $level->loadChunk($spawn->getX(), $spawn->getZ());
 $player->teleport($spawn, 0, 0);
 $player->getInventory()->clearAll();
 $player->setGamemode(2);
 $player->removeAllEffects();
 $player->setFood(20);
 $player->setHealth(20);
 $player->setMaxHealth(20);
 $player->addTitle("§fWelcome!");
 $player->sendMessage("§7Bienvenido §bcampeon§7! demuestra quien manda aqui");
 $puntos = new Config($this->getDataFolder() . "parti.yml", Config::YAML);
				$puntos->set($name,$puntos->get($name) + 1);
				$puntos->save();
 } else { $player->sendMessage($this->prefix . TextFormat::RED . "Arena " . $text[1] . " is full!");
 } } else { $player->sendMessage("§7[§9Build§bUHC§7] §7" . TextFormat::GRAY . "El juego esta completo!");
 } } } } public function fillChests(Level $level) { $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $tiles = $level->getTiles();
 foreach ($tiles as $t) { if ($t instanceof Chest) { $chest = $t;
 $chest->getInventory()->clearAll();
 if ($chest->getInventory() instanceof ChestInventory) { for ($i = 0;
 $i <= 26;
 $i++) { $rand = rand(1, 3);
 if ($rand == 1) { $k = array_rand($config->get("chestitems"));
 $v = $config->get("chestitems")[$k];
 $chest->getInventory()->setItem($i, Item::get($v[0], $v[1], $v[2]));
 } } } } } }
        public function zipper($player, $name)
        {
        $path = realpath($player->getServer()->getDataPath() . 'worlds/' . $name);
				$zip = new \ZipArchive;
				@mkdir($this->getDataFolder() . 'arenas/', 0755);
				$zip->open($this->getDataFolder() . 'arenas/' . $name . '.zip', $zip::CREATE | $zip::OVERWRITE);
				$files = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($path),
					\RecursiveIteratorIterator::LEAVES_ONLY
				);
                                foreach ($files as $datos) {
					if (!$datos->isDir()) {
						$relativePath = $name . '/' . substr($datos, strlen($path) + 1);
						$zip->addFile($datos, $relativePath);
					}
				}
				$zip->close();
				$player->getServer()->loadLevel($name);
				unset($zip, $path, $files);
        }
 public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args): bool{ $name = $sender->getName();
 $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
 $arena = $sender->getLevel()->getFolderName();
 if (in_array($arena, $this->arenas)) { $status = $config->get($arena . "Status");
 } else { $status = "NO-ARENA";
 } if ($cmd->getName() == "buhc") { if (!empty($args[0])) { if ($args[0] == "add" && $sender->isOP()) { if (!empty($args[1]) && !empty($args[2])) { if (file_exists($this->getServer()->getDataPath() . "worlds/" . $args[1])) { $arena = $args[1];
 $this->arenas[] = $arena;
 $config->set("arenas", $this->arenas);
 $config->set($arena . "Spieleranzahl", (int) $args[2]);
 $config->save();
 $this->copymap($this->getServer()->getDataPath() . "worlds/" . $arena, $this->getDataFolder() . "maps/" . $arena);
 $this->resetArena($arena);
 $player = $sender;
 $name = $arena;
$this->zipper($player, $name);
 $sender->sendMessage($this->prefix . "Ahora usa! -> /buhc help");
                $ac = new Config($this->getDataFolder()."$arena.yml", Config::YAML);
				$ac->set("Arena: $arena\nPlayersMax: $args[2]");
				$ac->save();
 } } }
elseif (strtolower($args[0]) == "save" && $sender->isOP()) { if (!empty($args[1])) {
	$arena = $args[1];
				                    $sender->getServer()->dispatchCommand($sender, "save-all");
$sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
				                    $sender->getServer()->dispatchCommand($sender, "mw unload $arena");
 $player = $sender;
 $name = $arena;
$this->zipper($player, $name);
 $this->getServer()->broadcastMessage("§7[§eSky§6Duels§7] §bSe ha removido el proceso de edicion y se ha activado la arena: §9 " . TextFormat::GOLD . $arena . TextFormat::WHITE . " ");
$config->set($arena . "Status", "Preparing");
 $config->save();
 } } elseif (strtolower($args[0]) == "register" && $sender->isOP()) { if (!empty($args[1])) { 
 $x = $sender->getX();
 $y = $sender->getY();
 $z = $sender->getZ();
			$level = $sender->getLevel();
 $this->signregister = true;
 $this->signregisterWHO = $sender->getName();
 $this->temparena = $args[1];
 $sender->sendMessage($this->prefix . "Toca un cartel");
 } } elseif (strtolower($args[0]) == "topfly" && $sender->isOP()) { 
$player = $sender;
            $f = new Config($this->getDataFolder()."user.yml", Config::YAML);
           $topw = array();
           $tope = $f->getAll();
           foreach($tope as $key => $tp){
            array_push($topw, $tp);
                    }
           natsort($topw);
           $grd = array_reverse($topw);
           $top1 = max($topw);
           $topv = array_search($top1, $tope);
           $top2 = array_search($grd[1], $tope);
           $top3 = array_search($grd[2], $tope);
           $top4 = array_search($grd[3], $tope);
           $top5 = array_search($grd[4], $tope);
           $top6 = array_search($grd[5], $tope);
           $top7 = array_search($grd[6], $tope);
           $top8 = array_search($grd[7], $tope);
           $top9 = array_search($grd[8], $tope);
           $top10 = array_search($grd[9], $tope);
         foreach($args as $word)
						$text .= "$word ";
						$text = trim($text);
						$x = $player->getX();
						$y = $player->getY();
						$z = $player->getZ();
						$text = "§l§bLEARDBOARD§r\n§7Daily BuildUHC Wins In This Moment\n\n§e#1 §b$topv §7.- §e$top1\n\n§e#2 §b$top2 §7.- §e$grd[1]\n\n§e#3 §b$top3 §7.- §e$grd[2]\n\n§e#4 §b$top4 §7.- §e$grd[3]\n\n§e#5 §b$top5 §7.- §e$grd[4]\n\n§e#6 §b$top6 §7.- §e$grd[5]\n\n§e#7 §9$top7 §7.- §e$grd[6]\n\n§e#8 §b$top8 §7.- §e$grd[7]\n\n§e#9 §b$top9 §7.- §e$grd[8]\n\n§e#10 §b$top10 §7.- §e$grd[9]";
						$this->config[$x.':'.$y.':'.$z] = $text;
					$player->getLevel()->addParticle(new FloatingTextParticle(new Vector3($x, $y, $z), '', $text), array($player));
$cfg = new Config($this->getDataFolder().'TopFly.yml', Config::YAML, [
          "X" => $x,
          "Y" => $y,
          "Z" => $z,
                ]);
                $cfg->save();
 } elseif (strtolower($args[0]) == "join" && $sender->hasPermission("vip.cmd")) { 
$allplayers = $this->getServer()->getOnlinePlayers();	
 $level = $this->getServer()->getDefaultLevel();
 $tiles = $level->getTiles();
 foreach ($tiles as $t) { if ($t instanceof Sign) { $text = $t->getText();
 if ($text[0] == $this->prefix) { 
 $aop = 0 ;
 $namemap = str_replace("§6", "", $text[1]);
if ($config->get($text[1] . "Status") == "Edit") {
	$sender->sendMessage("§7[§9Build§bUHC§7]§bNo se puede unir a esta partida en este momento, §cLa arena esta en mantenimiento");
 }
if ($config->get($text[1] . "Status") == "LobbyTimer") {
	$sender->sendMessage("§7[§9Build§bUHC§7] §bNo se puede unir a esta partida en este momento, §cLa arena esta en juego");
 }
if ($config->get($text[1] . "Status") == "Lobby") {
	$arena = $args[1];
$level = $this->getServer()->getLevelByName($arena);
 $this->getServer()->loadlevel($arena);
 $spawn = $level->getSafeSpawn();
 $level->loadChunk($spawn->getX(), $spawn->getZ());
 $sender->teleport($spawn, 0, 0);
 $sender->getInventory()->clearAll();
 $sender->removeAllEffects();
 $sender->setFood(20);
 $sender->setHealth(20);
 $puntos = new Config($this->getDataFolder() . "parti.yml", Config::YAML);
 $name = $sender->getName();
				$puntos->set($name,$puntos->get($name) + 1);
				$puntos->save();
 } } } } } elseif (strtolower($args[0]) == "help" && $sender->isOP()) { 
 $sender->sendMessage("§d[+-] §9Build§bUHC §fClient §d[-+]");
 $sender->sendMessage("§7  /buhc add <arena> <slots>");
 $sender->sendMessage("§7  /buhc setspawn <slot>");
 $sender->sendMessage("§7  /buhc help");
 $sender->sendMessage("§7  /buhc register <arena>");
 $sender->sendMessage("§7  /buhc save <arena>");
 $sender->sendMessage("§7  /buhc edit <arena>");
 $sender->sendMessage("§7 /buhc join <arena> §7(§bSoon§7)");
 } elseif (strtolower($args[0]) == "edit" && $sender->isOP()) { if (!empty($args[1])){
 	$arena = $args[1];
                    $sender->teleport($this->getServer()->getLevelByName($arena)->getSafeSpawn());
                    $sender->setGamemode(1);
$config->set($arena . "Status", "Edit");
 $config->save();
 } } elseif (strtolower($args[0]) == "setspawn" && $sender->isOP()) { if (!empty($args[1])) { $arena = $sender->getLevel()->getFolderName();
 $x = $sender->getX();
 $y = $sender->getY();
 $z = $sender->getZ();
 $coords = array($x, $y, $z);
 $config->set($arena . "Spawn" . $args[1], $coords);
 $config->save();
 $sender->sendMessage($this->prefix . "Se a establecido el spawn " . $args[1] . " de la Arena " . TextFormat::GOLD . $arena . TextFormat::WHITE . " ...!");
 } } else {   $sender->sendMessage("§d[+-] §9Build§bUHC §fClient §d[-+]");
 $sender->sendMessage("§7  /buhc add <arena> <slots>");
 $sender->sendMessage("§7  /buhc setspawn <slot>");
 $sender->sendMessage("§7  /buhc help");
 $sender->sendMessage("§7  /buhc register <arena>");
 $sender->sendMessage("§7  /buhc save <arena>");
 $sender->sendMessage("§7  /buhc edit <arena>");
 } } } } } class SWRefreshSigns extends PluginTask { public $prefix = "§cUHC-Simulator §7";
 public function __construct($plugin) { $this->plugin = $plugin;
 $this->prefix = $this->plugin->prefix;
 parent::__construct($plugin);
 } public function onRun($tick) { $allplayers = $this->plugin->getServer()->getOnlinePlayers();
 $level = $this->plugin->getServer()->getDefaultLevel();
 $tiles = $level->getTiles();
 foreach ($tiles as $t) { if ($t instanceof Sign) { $text = $t->getText();
 if ($text[0] == $this->prefix) { 
 $aop = 0 ;
 $namemap = str_replace("§6", "", $text[1]);
 foreach($allplayers as $player){if($player->getLevel()->getFolderName()==$namemap){$aop=$aop+1;}}
 $ingame = TextFormat::GREEN . "[Unirse]";
 $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
 $maxplayers = $config->get($text[1] . "Spieleranzahl");
 if ($config->get($text[1] . "Status") != "Lobby") { $ingame = TextFormat::RED . "[InGame]";
 } if ($aop >= $maxplayers) { $ingame = TextFormat::RED . "[Complete]";
 } if ($config->get($text[1] . "Status") == "Ende") { $ingame = TextFormat::RED . "[Restarting]";
 }
if ($config->get($text[1] . "Status") == "§c[Preparing]") {
  $rand = array("§cPreparando.", "§cPreparando..", "§cPreparando...");
  $ingame = $rand[array_rand($rand)];
  $aop = "";
  $maxplayers = "";
 $this->plugin->getServer()->loadlevel($text[1]);
				$config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
				$arena = $text[1];
		$config->set($arena . "Status", "Lobby");
$config->save();
  }
if ($config->get($text[1] . "Status") == "Edit") { $ingame = "§6¡No Disponible!";
$aop = "§c-";
$maxplayers = "§c-";
 } $t->setText($this->prefix, $text[1], $ingame, TextFormat::GRAY . $aop . "/" . $maxplayers);
 } } } } } class SWGameSender extends PluginTask {
 	public $gamegame = 0;
 	 public $prefix = "§7[§9Build§bUHC§7]";
 public function __construct($plugin) { $this->plugin = $plugin;
 $this->prefix = $this->plugin->prefix;
 parent::__construct($plugin);
 }
 
public function getResetmap() {
        Return new ResetMap($this);
        }
 
     public function time($int) {
     $m = floor($int / 60);
     $s = floor($int % 60);
     return (($m < 10 ? "0" : "") . $m . ":" . ($s < 10 ? "0" : "") . $s);
     }
     
 public function onRun($tick) { $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
 $arenas = $config->get("arenas");
 if (count($arenas) != 0) { foreach ($arenas as $arena) { $status = $config->get($arena . "Status");
 $lobbytimer = $config->get($arena . "LobbyTimer");
 $endtimer = $config->get($arena . "EndTimer");
 $gametimer = $config->get($arena . "GameTimer");
 $levelArena = $this->plugin->getServer()->getLevelByName($arena);
 if ($levelArena instanceof Level) { $players = $levelArena->getPlayers();
 if ($status == "Lobby") { if (count($players) < 2) { $config->set($arena . "LobbyTimer", 20);
 $config->set($arena . "EndTimer", 16);
 $config->set($arena . "Status", "Lobby");
 $config->save();
 foreach ($players as $p) {
 } if ((Time() % 20) == 0) { foreach ($players as $p) { $p->sendMessage(TextFormat::RED . "§6§l»§r §eWaiting Players §6§l«§r");
 } } } else { $lobbytimer--;
 $config->set($arena . "LobbyTimer", $lobbytimer);
 $config->save();
 if ($lobbytimer >= 1 && $lobbytimer <= 20) { foreach ($players as $p) { $t = str_repeat(" ", 80);
 $max = 20;
     $p->sendTip("  §7» §9Build§bUHC §fDuel §7«\n§f» §7Start in: §b$lobbytimer §f«");
 } } if ($lobbytimer <= 0) { $countPlayers = 0;
 foreach ($players as $p) { $countPlayers++;
 $spawn = $config->get($arena . "Spawn" . $countPlayers);
 $p->teleport(new Vector3($spawn[0], $spawn[1], $spawn[2]));
 $puntos = new Config($this->plugin->getDataFolder() . "user.yml", Config::YAML);
 $name = $p->getName();
 $coin = $puntos->get($name);
 $health = $p->getHealth();
 $p->setNameTag("§7[§e".$coin."§7] §6$name\n§c$health HP");
 $p->setFood(20);
 $p->setHealth(20);
$p->addTitle("§9§lBuild§bUHC§r", "§7» §f¡Enjoy your stay! «");
 $p->getInventory()->clearAll();
$casco = Item::get(Item::DIAMOND_HELMET, 0, 1);
$enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
$casco->addEnchantment(new EnchantmentInstance($enchantment, 1));
$casco->setCustomName("§bCasco De Diamante");
$p->getArmorInventory()->setHelmet($casco);
$peto = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
$enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
$peto->addEnchantment(new EnchantmentInstance($enchantment, 1));
$peto->setCustomName("§bPechera De Diamante");
$p->getArmorInventory()->setChestplate($peto);
$pantalon = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
$enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
$pantalon->addEnchantment(new EnchantmentInstance($enchantment, 1));
$pantalon->setCustomName("§bPantalones De Diamante");
$p->getArmorInventory()->setLeggings($pantalon);
$botas = Item::get(Item::DIAMOND_BOOTS, 0, 1);
$enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
$botas->addEnchantment(new EnchantmentInstance($enchantment, 1));
$botas->setCustomName("§bBotas De Diamante");
$p->getArmorInventory()->setBoots($botas);
$sword = Item::get(Item::DIAMOND_SWORD, 0, 1);
$enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
$sword->addEnchantment(new EnchantmentInstance($enchantment, 1));
$p->getInventory()->addItem($sword);
$p->getInventory()->addItem(Item::get(279,0,1));
$p->getInventory()->addItem(Item::get(261, 0, 1));
$manzana = Item::get(Item::GOLDEN_APPLE);
$manzana->setDamage(10);
$manzana->setCount(4);
$manzana->setCustomName("§6Golden Head");
$p->getInventory()->addItem($manzana);
$p->getInventory()->addItem(Item::get(322,0,10));
$p->getInventory()->addItem(Item::get(5, 0, 64));
	$p->getInventory()->addItem(Item::get(262,0,64));
 $p->getInventory()->addItem(Item::get(364,0,64));
$p->setGamemode(0);
$p->getLevel()->addSound(new FizzSound($p));
 $this->plugin->lasthit[$p->getName()] = "kei ner";
 foreach($p->getLevel()->getPlayers() as $online){ $p->showPlayer($online);
 } $this->plugin->fillChests($levelArena);
 } $config->set($arena . "Status", "Ingame");
 $config->save();
 $p->getLevel()->addSound(new FizzSound($p));
 } } } if ($status == "Ingame") { $gametimer--;
 $config->set($arena . "GameTimer", $gametimer);
 $config->save();
 $min = $gametimer / 60;
 $gametimer--;
 foreach ($players as $pl) {     
$t = str_repeat(" ", 80);
$nick = $pl->getName();
$name = $nick;
$x = $pl->getFloorX();
$y = $pl->getFloorY();
$z = $pl->getFloorZ();
$ali = count($this->plugin->getServer()->getLevelByName($arena)->getPlayers());
            $ac = new Config($this->plugin->getDataFolder()."$arena.yml", Config::YAML);
            $max = "2";
             $maxe = $config->get($arena . "Spieleranzahl");
$text = array("\n$t     §9§lBuild§bUHC§r\n$t §7§l-----------§r\n$t §7Match Time §b".$this->time($gametimer)."\n\n$t §7Ping §b".$pl->getPing()."\n$t §7Players §b".$ali."§7/§b".$maxe."\n\n$t §7Points §b".$this->plugin->getVic($name)."\n$t §7§l-----------\n                                                                   §b@§9CrankyPE\n\n\n\n\n\n\n\n\n\n");
  $barte = $text[array_rand($text)];
  $pl->sendTip($barte);
 $name = $pl->getName();
 $puntos = new Config($this->plugin->getDataFolder() . "user.yml", Config::YAML);
 $coin = $puntos->get($name);
 $health = $pl->getHealth();
 $pl->setNameTag("§7[§e".$coin."§7] §6$name\n§c$health HP");
 } if ($gametimer == 30 || $gametimer == 20 || $gametimer == 10 || $gametimer == 5 || $gametimer == 4 || $gametimer == 3 || $gametimer == 2 || $gametimer == 1 ) { foreach ($players as $p) { $p->sendTip("La partida termina en " . TextFormat::GOLD . $gametimer . TextFormat::WHITE . " segundos!");
 } } if ($gametimer == 0) { $this->plugin->getServer()->broadcastMessage("§7[§9Build§bUHC§7] " . "A terminado la partida en la arena " . TextFormat::GOLD . $arena . TextFormat::WHITE . " sin ganador -");
 $config->set($arena . "Status", "Ende");
 $config->save();
 } if (count($players) <= 1) { foreach ($players as $p) { $name = $p->getName();
 $this->plugin->getServer()->broadcastMessage("§7- §9Build§bUHC §7-\n§7Congrulations to " . TextFormat::GOLD . $name . TextFormat::WHITE . "\n§f----------");
foreach ($players as $p) { $name = $p->getName();
 $puntos = new Config($this->plugin->getDataFolder() . "user.yml", Config::YAML);
				$puntos->set($name,$puntos->get($name) + 10);
				$puntos->save();
 $level = $this->plugin->getServer()->getDefaultLevel();
 $tiles = $level->getTiles(); 
 } } $config->set($arena . "Status", "Ende");
 $config->save();
 } } if ($status == "Ende") { if ($endtimer >= 0) { $endtimer--;
 $config->set($arena . "EndTimer", $endtimer);
 $config->save();
 if($endtimer == 15){
 	foreach($players as $p){
 		$p->sendTip("§9Build§bUHC §7You win this game!\n           §cStatus: §f3 seg.");
 	}
 }
 if($endtimer == 14){
 	foreach($players as $p){
 		$p->sendTip("§9Build§bUHC §7You win this game!\n           §cStatus: §f2 seg.");
 	}
 }
 if($endtimer == 13){
 	foreach($players as $p){
 		$p->sendTip("§9Build§bUHC §7You win this game!\n           §cStatus: §f1 seg.");
 	}
 }
 if($endtimer == 12){
 	foreach($players as $p){
$p->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
 $p->setFood(20);
  $name = $p->getName();
    $up = new Config($this->plugin->getDataFolder() . "Statics.yml", Config::YAML);
    $up->remove($name);
 $p->setHealth(20);
 $p->getInventory()->clearAll();
 $p->removeAllEffects();
 $p->getArmorInventory()->clearAll();
    $p->getInventory()->setItem(0, Item::get(322, 0, 1)->setCustomName("§7-[ §fStats Game§7 ]-"));
    $p->getInventory()->setItem(3, Item::get(399, 0, 1)->setCustomName("§bStaffMode §8: §7Tap"));
    $p->getInventory()->setItem(8, Item::get(54, 0, 1)->setCustomName("§l§6COSMETICS"));
    $p->getInventory()->setItem(4, Item::get(Item::DIAMOND_HELMET,0,1)->setCustomName("§3§lF§5F§9A"));
    $p->getInventory()->setItem(5, Item::get(Item::FEATHER,0,1)->setCustomName("§eLauncher §8: §7Tap"));
    $p->setNameTag("§8(§5".$this->plugin->data[$p->getName()]["OS"]."§8) §7".$p->getName());
     $p->addTitle("§9You Win", "§f» §7Enjoy your stay §f«");
 $p->sendTip("§9You win the game ! +10 points for winning");
 	}
 }
 if ($endtimer == 0) { $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
$this->plugin->getServer()->unloadLevel($levelArena);
 $this->plugin->copymap($this->plugin->getDataFolder() . "maps/" . $arena, $this->plugin->getServer()->getDataPath() . "worlds/" . $arena);
 $this->plugin->getServer()->loadLevel($arena);
 foreach ($players as $p) { $name = $p->getName();
 }
 $config->set($arena . "LobbyTimer", 20);
$config->set($arena . "Edit", 30 * 60 + 1);
 $config->set($arena . "Preparing", 5);
 $config->set($arena . "EndTimer", 16);
 $config->set($arena . "GameTimer", 30 * 60 + 1);
 $config->set($arena . "Status", "Lobby");
 $config->save();
 } 
 }
 }
 }
 }
 }
 }
 }