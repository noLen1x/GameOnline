<?php

declare(strict_types=1);

namespace nolenix\GameOnline\command;

use pocketmine\Player;
use pocketmine\command\{
	Command, 
	CommandSender};

use nolenix\GameOnline\Loader;

class OnlineCommand extends Command
{

    /** @var \ Loader */
    private $loader;

	public function __construct(Loader $loader)
	{
        $this->loader = $loader;
		parent::__construct("online", "See information about the played time");
        $this->setPermission("command.online");
	}

	/**
	 * @param CommandSender      $player
	 * @param $alias
	 * @param array        $args
	 */

	public function execute(CommandSender $player, $alias, array $args) : bool 
	{
	    if($player instanceof Player)
		{
			$time = explode(":", $this->loader->getTotalTime($player->getName()));

		    $player->sendMessage("§c⤿ §fYou have: " . $time[0] . "§bH §f" . $time[1] . "§bM §f" . $time[2] . "§bS §fto play.");
		    return true;
		}
		
		return false;
	}

}