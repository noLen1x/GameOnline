# GameOnline

Plugin is designed for the PMMP3 software. Active core for the plugin: Prismarine.

The plugin adds to your server the ability to view the game time with the support of the database SqLite3.

# Commands

/online - View your own game time
![l5RTb4-a2B0](https://user-images.githubusercontent.com/119180406/223235009-2a37c819-5f57-4bce-a6ea-e670486cde17.jpg)

# For developers

The plugin has a method for further work with the online-tops.

```
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
```
