<?php

namespace nolenix\GameOnline\database;

use pocketmine\Player;
use pocketmine\plugin\PluginException;

use nolenix\GameOnline\Loader;

class SQLite
{
    /** @var \SQLite3 */
    private $database;

    /** @var \ Loader */
    private $loader;

    /**
     * SQLiteProvider constructor.
     *
     * @param Loader $loader
     */

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
        $this->database = new \SQLite3($loader->getDataFolder() . "players.db");
        $query = "CREATE TABLE IF NOT EXISTS players(uuid VARCHAR(36), username VARCHAR(16), time INT);";
        $this->database->exec($query);
    }

    /**
     * @return \SQLite3
     */

    public function getDatabase(): \SQLite3
    {
        return $this->database;
    }

    /**
     * @param $player
     *
     * @return int|null
     */

    public function getRawTime($player): ?int
    {
        if ($player instanceof Player) 
        {
            $uuid = $player->getRawUniqueId();
            $query = "SELECT time FROM players WHERE uuid = :uuid";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(":uuid", $uuid);
            $result = $stmt->execute();

            return $result->fetchArray(SQLITE3_ASSOC)["time"];
        }

        if (is_string($player)) 
        {
            $query = "SELECT time FROM players WHERE username = :username COLLATE NOCASE";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(":username", strtolower($player));
            $result = $stmt->execute();

            return $result->fetchArray(SQLITE3_ASSOC)["time"];
        }
        return null;
    }

    /**
     * @param $player
     *
     * @return bool
     */

    public function hasTime($player) : bool
    {
        if ($player instanceof Player) 
        {
            $uuid = $player->getRawUniqueId();
            $query = "SELECT time FROM players WHERE uuid = :uuid";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(":uuid", $uuid);
            $result = $stmt->execute();
            return $result->fetchArray(SQLITE3_ASSOC)["time"] !== null ? true : false;
        }

        if (is_string($player)) 
        {
            $query = "SELECT time FROM players WHERE username = :username COLLATE NOCASE";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(":username", strtolower($player));
            $result = $stmt->execute();
            return $result->fetchArray(SQLITE3_ASSOC)["time"] !== null ? true : false;
        }

        return false;
    }

    /**
     * @param Player $player
     */

    public function registerTime(Player $player) : void
    {
        $uuid = $player->getRawUniqueId();
        $username = strtolower($player->getName());
        $query = "INSERT INTO players(uuid, username, time) VALUES(:uuid, :username, :time);";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(":uuid", $uuid);
        $stmt->bindValue(":username", $username);
        $stmt->bindValue(":time", 0);
        $stmt->execute();
    }

    /**
     * @param $player
     * @param int $time
     *
     * @throws PluginException
     */

    public function setRawTime($player, int $time) : void
    {
        if ($player instanceof Player) 
        {
            $uuid = $player->getRawUniqueId();
            $query = "UPDATE players SET time = :time WHERE uuid = :uuid";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(":time", $time);
            $stmt->bindValue(":uuid", $uuid);
            $stmt->execute();
            return;
        }

        if (is_string($player)) 
        {
            $query = "UPDATE players SET time = :time WHERE username = :username COLLATE NOCASE";
            $stmt = $this->database->prepare($query);
            $stmt->bindValue(":time", $time);
            $stmt->bindValue(":username", strtolower($player));
            $stmt->execute();
            return;
        }
        
        throw new PluginException("Failed to set the time of a player that doesn't exist: $player");
    }
}
