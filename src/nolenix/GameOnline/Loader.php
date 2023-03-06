<?php

declare(strict_types=1);

namespace nolenix\GameOnline;

use pocketmine\plugin\PluginBase;

use nolenix\GameOnline\database\SQLite;
use nolenix\GameOnline\event\EventListener;
use nolenix\GameOnline\command\OnlineCommand;

final class Loader extends PluginBase
{
    /** @var self */
    public static $times = [];

    /** @var SQLite */
    public $db;

    public function onEnable() : void
    {
        $this->db = new SQLite($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("online", new OnlineCommand($this));
    }

    /**
     * @param int $cPage
     *
     * @return string|null
     */

    public function getTopUsers(int $cPage) : ?string
    {
        $query = "SELECT username, time FROM players ORDER BY time;";
        $result = $this->db->getDatabase()->query($query);
        $place = 1;
        $data = [];
        $start = microtime(true);

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) 
        {
            $data[$row["username"]] = $row["time"];
            $place++;
        }

        arsort($data);

        $i = 0;
        $pagelength = 10;
        $n = count($data);
        $pages = round($n / $pagelength);
        $page = 1;

        if (isset($cPage) && is_numeric($cPage)) 
        {
            if ($cPage > ($n / $pagelength)) 
                return null;

            $page = $cPage;
        }

        /*
        The ability to get the top page.

        For example:
            "Page $page from $pages"
        */

        foreach ($data as $key => $val) 
        {
            $i++;
            if ($i >= $pagelength * ($page - 1) && $i <= (($pagelength * ($page - 1)) + 10)) 
            {
                $session = in_array($key, $this->getServer()->getOnlinePlayers()) ? self::$times[$key] : 0;

                $formattedtime = $this->getFormattedTime(($val + $session));

                return "§e$i. §f$key: §b" . $formattedtime;
            }
        }

        return null;
    }

    /**
     * @param $t
     *
     * @return strng
     */

    public function getFormattedTime($t)
    {
        $f = sprintf("%02d%s%02d%s%02d", floor(abs($t) / 3600), ":", (abs($t) / 60) % 60, ":", abs($t) % 60);
        $time = explode(":", $f);
        return $time[0] . "§9 час. §b" . $time[1] . "§9 мин. §b" . $time[2] . "§9 сек.";
    }

    /**
     * @param $pn
     *
     * @return int|null
     */

    public function getTotalTime($pn): string
    {
        $pn = strtolower($pn);

        if ($this->getServer()->getPlayer($pn) !== null) 
        {
            $p = $this->getServer()->getPlayer($pn);
        } 
        else 
            $p = $pn;

        $totalsecs = $this->db->getRawTime($p);

        if ($this->getServer()->getPlayer($pn) !== null) 
        {
            $t = (time() - self::$times[$pn]);
        } 
        else 
            $t = 0;

        $t = ($t + $totalsecs);

        return ($t < 0 ? '-' : '') . sprintf("%02d%s%02d%s%02d", floor(abs($t) / 3600), ":", (abs($t) / 60) % 60, ":", abs($t) % 60);
    }

    public function onDisable() : void
    {
        foreach (self::$times as $player => $time) 
        {
            $player = strtolower($player);

            if ($this->getServer()->getPlayer($player) !== null) 
            {
                $p = $this->getServer()->getPlayer($player);
            } 
            else 
                $p = $player;

            $old = $this->db->getRawTime($p);
            $this->db->setRawTime($p, ($old + (time() - self::$times[$player])));
            unset(self::$times[$player]);
        }
    }
}
