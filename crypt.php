<?php

class Crypter
{
    private static $Crypter = "!@#Crypter#@!";
    private static function key_gen($str, $len)
    {
        $arr = [];
        $key_len = (int)(mb_strlen($str) / $len) + 1;
        for ($i = 0; $i < $key_len; $i++) {
            if (mb_strlen($str) < $len) $str = $str . str_repeat("$", $len - mb_strlen($str));
            $arr[] = substr($str, 0, $len);
            $str = substr($str, $len);
        }
        return $arr;
    }

    private static function strtonum($data, $p = 0)
    {
        $new_string = "";
        $alphabet =  range("a", "z");
        $string_arr = str_split(strtolower(preg_replace('/[^\w]/', '', $data)));
        foreach ($string_arr as $str) {
            $new_string .= is_numeric($str) ? $str : array_search($str, $alphabet);
        }
        return (int)substr($new_string, $new_string[$p], 10);
    }
    private static function seeded_shuffle($items, $seed)
    {
        $seed = self::strtonum($seed);
        $items = str_split($items);
        $items = array_values($items);
        mt_srand($seed);
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            list($items[$i], $items[$j]) = array($items[$j], $items[$i]);
        }
        return implode("", $items);
    }
    private static function seeded_unshuffle($items, $seed)
    {
        $seed = self::strtonum($seed);
        $items = str_split($items);
        $items = array_values($items);

        mt_srand($seed);
        $indices = [];
        for ($i = count($items) - 1; $i > 0; $i--) {
            $indices[$i] = mt_rand(0, $i);
        }

        foreach (array_reverse($indices, true) as $i => $j) {
            list($items[$i], $items[$j]) = [$items[$j], $items[$i]];
        }
        return implode("", $items);
    }
    private static function get_key($master_arr, $p)
    {
        $c = count($master_arr);
        while ($c <= $p) {
            $p = $p - $c;
        }
        return $master_arr[$p];
    }
    public static function crypt($str, $master_key = "", int $dificulty = 1)
    {
        $str = self::$Crypter.$str;
        $str = base64_encode($str);
        $master_hash = hash("sha512", $master_key);
        $master_arr = self::key_gen($master_hash, $dificulty);
        $crypt = "";
        $len = mb_strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $crypt .= $str[$i] . self::get_key($master_arr, $i);
        }
        $crypt = (self::seeded_shuffle(self::seeded_shuffle(base64_encode($crypt), $master_hash), $master_hash, 1));
        $crypt = base64_encode($crypt);
        return $crypt;
    }
    public static function decrypt($str, $master_key = "", int $dificulty = 1)
    {
        $master_hash = hash("sha512", $master_key);
        $decrypt = base64_decode($str);
        $decrypt = self::seeded_unshuffle( $decrypt, $master_hash );
        $decrypt = self::seeded_unshuffle( $decrypt, $master_hash, 1 );
        $decrypt = base64_decode($decrypt);

        $auto = str_split($decrypt, $dificulty + 1);
        $decrypt = "";
        foreach ($auto as $get) {
            $decrypt .= substr($get, 0, 1);
        }
        $decrypt = base64_decode($decrypt);
        if(substr($decrypt, 0, strlen(self::$Crypter)) != self::$Crypter) return 0;
        $decrypt = substr($decrypt, strlen(self::$Crypter));
        return $decrypt;
    }
}
$hash_this = "Zdravo!";
$master_key = "Nemanja!@$%";
$dificulty = 1;

$c = new Crypter;
$crypt = $c->crypt($hash_this, $master_key, $dificulty);
$decrypt = $c->decrypt($crypt, $master_key, $dificulty);

echo "\n**************************";
echo "\n**************************\n";
echo "CRYPT -> " . $crypt;
echo "\n\nDECRYPT -> " . $decrypt;
echo "\n\nGLUP -> " . $c->decrypt($crypt, "Nemanja");
echo "\n**************************";
echo "\n**************************\n";
