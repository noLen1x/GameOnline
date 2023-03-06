<?php

declare(strict_types=1);

namespace nolenix\GameOnline\event;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\event\player\{
    PlayerJoinEvent,
    PlayerQuitEvent};

use nolenix\GameOnline\Loader;

final class EventListener extends PluginBase implements Listener
{

    /** @var \ Loader */
    private $loader;

	public function __construct(Loader $loader) 
	{
        $this->loader = $loader;
	}

    /**
     * @return void
    */

	public function onJoin(PlayerJoinEvent $event) : void
    {
        if ($this->loader->db->hasTime($event->getPlayer()) === false) 
        {
            $this->loader->db->registerTime($event->getPlayer());
        }

        $name = strtolower($event->getPlayer()->getName());
        Loader::$times[$name] = time();
    }

    /**
     * @return void
    */

    public function onQuit(PlayerQuitEvent $event) : void
    {
        $name = strtolower($event->getPlayer()->getName());

        if (isset(Loader::$times[$name])) 
        {
            $old = $this->loader->db->getRawTime($event->getPlayer());
            $this->loader->db->setRawTime($event->getPlayer(), ($old + (time() - Loader::$times[$name])));

            unset(Loader::$times[$name]);
        }
    }
}
