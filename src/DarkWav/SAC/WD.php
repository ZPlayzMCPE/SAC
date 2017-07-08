<?php

namespace DarkWav\SAC;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\Plugin;
use pocketmine\plugin\PluginLoader;
use DarkWav\SAC\EventListener;
use DarkWav\SAC\Observer;
use DarkWav\SAC\KickTask;

class WD extends PluginBase
{
  public $Config;
  public $Logger;
  public $cl;
  public $PlayerObservers = array();
  public $PlayersToKick   = array();

  public function onEnable()
  {
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new KickTask($this), 1);
    @mkdir($this->getDataFolder());
    $this->saveDefaultConfig();
    $this->saveResource("AntiForceOP.txt");
    $this->saveResource("AntiForceGM.txt");
    $cl              = $this->getConfig()->get("Color");
  
    $Config = $this->getConfig();
    $Logger = $this->getServer()->getLogger();
    $Server = $this->getServer();
    
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    $Logger->info(TextFormat::ESCAPE."$cl" . "[WD] > WatchDog Activated"            );
    $Logger->info(TextFormat::ESCAPE."$cl" . "[WD] > WatchDog v3.2.5 [Shade]");
    $Logger->info(TextFormat::ESCAPE."$cl" . "[WD] > Loading Modules");
    if($Config->get("ForceOP"    )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiForceOP"    );
    if($Config->get("NoClip"     )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiNoClip"     );
    if($Config->get("Fly"        )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiFly"        );
    if($Config->get("Fly"        )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiJesus"      );
    if($Config->get("Fly"        )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiSpider"     );
    if($Config->get("Glide"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiGlide"      );
    if($Config->get("KillAura"   )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiKillAura"   );
    if($Config->get("Reach"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiReach"      );
    if($Config->get("Speed"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiSpeed"      );
    if($Config->get("Regen"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > Enabling AntiRegen"      );

    if($Config->get("Config-Version") !== "3.5.5")
    {
      $Logger->warning(TextFormat::ESCAPE."$cl"."[WD] > Your Config is out of date!");
    }
    if($Config->get("Plugin-Version") !== "3.2.5" and $Config->get("Plugin-Version") !== "3.2.4" and $Config->get("Plugin-Version") !== "3.2.3")
    {
      $Logger->error(TextFormat::ESCAPE."$cl"."[WD] > Your Config is incompatible with this plugin version, please update immediately!");
      $Server->shutdown();
    }

    foreach($Server->getOnlinePlayers() as $player)
    {
      $hash     = spl_object_hash($player);
      $name     = $player->getName();
      $oldhash  = null;
      $observer = null;
      
      foreach ($this->PlayerObservers as $key=>$obs)
      {
        if ($obs->PlayerName == $name)
        {
          $oldhash  = $key;
          $observer = $obs;
          $observer->Player = $player;
        }
      }
      if ($oldhash != null)
      {
        unset($this->PlayerObservers[$oldhash]);
        $this->PlayerObservers[$hash] = $observer;
        $this->PlayerObservers[$hash]->PlayerRejoin();
      }  
      else
      {
        $observer = new Observer($player, $this);
        $this->PlayerObservers[$hash] = $observer;
        $this->PlayerObservers[$hash]->PlayerJoin();      
      }      
    }  
  }

  public function onDisable()
  {
    $cl              = $this->getConfig()->get("Color");
    $Logger = $this->getServer()->getLogger();
    $Server = $this->getServer();

    $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > You are no longer protected from cheats!");
    $Logger->info(TextFormat::ESCAPE."$cl"."[WD] > WatchDog Deactivated");
    $Server->enablePlugin($this);
  }
    
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
  {
    $Logger = $this->getServer()->getLogger();
    $cl              = $this->getConfig()->get("Color");
    if ($this->getConfig()->get("ForceOP"))
    {
      if ($sender->isOp())
      {
        if (!$sender->hasPermission($this->getConfig()->get("ForceOP-Permission")))
        {
          if ($sender instanceof Player)
          {
            $sname = $sender->getName();
            $message  = "[WD] > $sname used ForceOP!";
            $this->NotifyAdmins($message);
            $sender->getPlayer()->kick(TextFormat::ESCAPE."$cl"."[WD] > ForceOP detected!");
          }
        }
      }
    }
    if ($cmd->getName() === "wd" or $cmd->getName() === "watchdog")
    {
      $sender->sendMessage(TextFormat::ESCAPE."$cl"."[WD] > WatchDog v3.2.5 [Shade] (~DarkWav/Darku)");
    }
  }
  
  public function NotifyAdmins($message)
  {
    $cl              = $this->getConfig()->get("Color");
    if($this->getConfig()->get("Verbose"))
    {
      foreach ($this->PlayerObservers as $observer)
      {
        $player = $observer->Player;
        if ($player != null and $player->hasPermission("wd.admin"))
        {
          $player->sendMessage(TextFormat::ESCAPE."$cl" . $message);
        }
      }
    }  
  }  
  
}

//////////////////////////////////////////////////////
//                                                  //
//     SAC by DarkWav.                              //
//     Distributed under the AntiCheat License.     //
//     Do not redistribute in modyfied form!        //
//     All rights reserved.                         //
//                                                  //
//////////////////////////////////////////////////////
