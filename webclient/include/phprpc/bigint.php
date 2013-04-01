<?php
/**********************************************************\
|                                                          |
| The implementation of PHPRPC Protocol 3.0                |
|                                                          |
| bigint.php                                               |
|                                                          |
| Release 3.0.1                                            |
| Copyright (c) 2005-2008 by Team-PHPRPC                   |
|                                                          |
| WebSite:  http://www.phprpc.org/                         |
|           http://www.phprpc.net/                         |
|           http://www.phprpc.com/                         |
|           http://sourceforge.net/projects/php-rpc/       |
|                                                          |
| Authors:  Ma Bingyao <andot@ujn.edu.cn>                  |
|                                                          |
| This file may be distributed and/or modified under the   |
| terms of the GNU Lesser General Public License (LGPL)    |
| version 3.0 as published by the Free Software Foundation |
| and appearing in the included file LICENSE.              |
|                                                          |
\**********************************************************/

/* Big integer expansion library.
 *
 * Copyright (C) 2006-2008 Ma Bingyao <andot@ujn.edu.cn>
 * Version: 3.0
 * LastModified: May 13, 2007
 * This library is free.  You can redistribute it and/or modify it.
 */

if (extension_loaded('gmp')) {
    function bigint_dec2num($dec) {
        return gmp_init($dec);
    }
    function bigint_num2dec($num) {
        return gmp_strval($num);
    }
    function bigint_str2num($str) {
        return gmp_init("0x".bin2hex($str));
    }
    function bigint_num2str($num) {
        $str = gmp_strval($num, 16);
        $len = strlen($str);
        if ($len % 2 == 1) {
            $str = '0'.$str;
        }
        return pack("H*", $str);
    }
    function bigint_random($n, $s) {
        $result = gmp_init(0);
        for ($i = 0; $i < $n; $i++) {
            if (mt_rand(0, 1)) {
                gmp_setbit($result, $i);
            }
        }
        if ($s) {
            gmp_setbit($result, $n - 1);
        }
        return $result;
    }
    function bigint_powmod($x, $y, $m) {
        return gmp_powm($x, $y, $m);
    }
}
else if (extension_loaded('big_int')) {
    function bigint_dec2num($dec) {
        return bi_from_str($dec);
    }
    function bigint_num2dec($num) {
        return bi_to_str($num);
    }
    function bigint_str2num($str) {
        return bi_from_str(bin2hex($str), 16);
    }
    function bigint_num2str($num) {
        $str = bi_to_str($num, 16);
        $len = strlen($str);
        if ($len % 2 == 1) {
            $str = '0'.$str;
        }
        return pack("H*", $str);
    }
    function bigint_random($n, $s) {
        $result = bi_rand($n);
        if ($s) {
            $result = bi_set_bit($result, $n - 1);
        }
        return $result;
    }
    function bigint_powmod($x, $y, $m) {
        return bi_powmod($x, $y, $m);
    }
}
else if (extension_loaded('bcmath')) {
    function bigint_dec2num($dec) {
        return $dec;
    }
    function bigint_num2dec($num) {
        return $num;
    }
    function bigint_str2num($str) {
        $len = strlen($str);
        $result = '0';
        $m = '1';
        for ($i = 0; $i < $len; $i++) {
            $result = bcadd(bcmul($m, ord($str{$len - $i - 1})), $result);
            $m = bcmul($m, '256');
        }
        return $result;
    }
    function bigint_num2str($num) {
        $str = "";
        while (bccomp($num, '0') == 1) {
           $str = chr(bcmod($num, '256')) . $str;
           $num = bcdiv($num, '256');
        }
        return $str;
    }
    function bigint_random($n, $s) {
        $lowBitMasks = array(0x0000, 0x0001, 0x0003, 0x0007,
                             0x000f, 0x001f, 0x003f, 0x007f,
                             0x00ff, 0x01ff, 0x03ff, 0x07ff,
                             0x0fff, 0x1fff, 0x3fff, 0x7fff);
        $r = $n % 16;
        $q = floor($n / 16);
        $result = '0';
        $m = '1';
        for ($i = 0; $i < $q; $i++) {
            $rand = mt_rand(0, 0xffff);
            if (($q - 1 == $i) and ($r == 0) and ($s == 1)) {
                $rand |= 0x8000;
            }
            $result = bcadd(bcmul($m, $rand), $result);
            $m = bcmul($m, '65536');
        }
        if ($r != 0) {
            $rand = mt_rand(0, $lowBitMasks[$r]);
            if ($s == 1) {
                $rand |= 1 << ($r - 1);
            }
            $result = bcadd(bcmul($m, $rand), $result);
        }
        return $result;
    }
    if (!function_exists('bcpowmod')) {
        function bcpowmod($x, $y, $modulus, $scale = 0) {
            $t = '1';
            while (bccomp($y, '0')) {
                if (bccomp(bcmod($y, '2'), '0')) {
                    $t = bcmod(bcmul($t, $x), $modulus);
                    $y = bcsub($y, '1');
                }

                $x = bcmod(bcmul($x, $x), $modulus);
                $y = bcdiv($y, '2');
            }
            return $t;
        }
    }
    function bigint_powmod($x, $y, $m) {
        return bcpowmod($x, $y, $m);
    }
}
else {
    function bigint_mul($a, $b) {
        $n = count($a);
        $m = count($b);
        $nm = $n + $m;
        $c = array_fill(0, $nm, 0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $m; $j++) {
                $c[$i + $j] += $a[$i] * $b[$j];
                $c[$i + $j + 1] += ($c[$i + $j] >> 15) & 0x7fff;
                $c[$i + $j] &= 0x7fff;
            }
        }
        return $c;
    }
    function bigint_div($a, $b, $is_mod = 0) {
        $n = count($a);
        $m = count($b);
        $c = array();
        $d = floor(0x8000 / ($b[$m - 1] + 1));
        $a = bigint_mul($a, array($d));
        $b = bigint_mul($b, array($d));
        for ($j = $n - $m; $j >= 0; $j--) {
            $tmp = $a[$j + $m] * 0x8000 + $a[$j + $m - 1];
            $rr = $tmp % $b[$m - 1];
            $qq = round(($tmp - $rr) / $b[$m - 1]);
            if (($qq == 0x8000) || (($m > 1) && ($qq * $b[$m - 2] > 0x8000 * $rr + $a[$j + $m - 2]))) {
                $qq--;
                $rr += $b[$m - 1];
                if (($rr < 0x8000) && ($qq * $b[$m - 2] > 0x8000 * $rr + $a[$j + $m - 2])) $qq--;
            }
            for ($i = 0; $i < $m; $i++) {
                $tmp = $i + $j;
                $a[$tmp] -= $b[$i] * $qq;
                $a[$tmp + 1] += floor($a[$tmp] / 0x8000);
                $a[$tmp] &= 0x7fff;
            }
            $c[$j] = $qq;
            if ($a[$tmp + 1] < 0) {
                $c[$j]--;
                for ($i = 0; $i < $m; $i++) {
                    $tmp = $i + $j;
                    $a[$tmp] += $b[$i];
                    if ($a[$tmp] > 0x7fff) {
                        $a[$tmp + 1]++;
                        $a[$tmp] &= 0x7fff;
                    }
                }
            }
        }
        if (!$is_mod) return $c;
        $b = array();
        for ($i = 0; $i < $m; $i++) $b[$i] = $a[$i];
        return bigint_div($b, array($d));
    }
    function bigint_zerofill($str, $num) {
        return str_pad($str, $num, '0', STR_PAD_LEFT);
    }
    function bigint_dec2num($dec) {
        $n = strlen($dec);
        $a = array(0);
        $n += 4 - ($n % 4);
        $dec = bigint_zerofill($dec, $n);
        $n >>= 2;
        for ($i = 0; $i < $n; $i++) {
            $a = bigint_mul($a, array(10000));
            $a[0] += (int)substr($dec, 4 * $i, 4);
            $m = count($a);
            $j = 0;
            $a[$m] = 0;
            while ($j < $m && $a[$j] > 0x7fff) {
                $a[$j++] &= 0x7fff;
                $a[$j]++;
            }
            while ((count($a) > 1) && (!$a[count($a) - 1])) array_pop($a);
        }
        return $a;
    }
    function bigint_num2dec($num) {
        $n = count($num) << 1;
        $b = array();
        for ($i = 0; $i < $n; $i++) {
            $tmp = bigint_div($num, array(10000), 1);
            $b[$i] = bigint_zerofill($tmp[0], 4);
            $num = bigint_div($num, array(10000));
        }
        while ((count($b) > 1) && !(int)$b[count($b) - 1]) array_pop($b);
        $n = count($b) - 1;
        $b[$n] = (int)$b[$n];
        $b = join('', array_reverse($b));
        return $b;
    }
    function bigint_str2num($str) {
        $n = strlen($str);
        $n += 15 - ($n % 15);
        $str = str_pad($str, $n, chr(0), STR_PAD_LEFT);
        $j = 0;
        $result = array();
        for ($i = 0; $i < $n; $i++) {
            $result[$j++] = (ord($str{$i++}) << 7) | (ord($str{$i}) >> 1);
            $result[$j++] = ((ord($str{$i++}) & 0x01) << 14) | (ord($str{$i++}) << 6) | (ord($str{$i}) >> 2);
            $result[$j++] = ((ord($str{$i++}) & 0x03) << 13) | (ord($str{$i++}) << 5) | (ord($str{$i}) >> 3);
            $result[$j++] = ((ord($str{$i++}) & 0x07) << 12) | (ord($str{$i++}) << 4) | (ord($str{$i}) >> 4);
            $result[$j++] = ((ord($str{$i++}) & 0x0f) << 11) | (ord($str{$i++}) << 3) | (ord($str{$i}) >> 5);
            $result[$j++] = ((ord($str{$i++}) & 0x1f) << 10) | (ord($str{$i++}) << 2) | (ord($str{$i}) >> 6);
            $result[$j++] = ((ord($str{$i++}) & 0x3f) << 9) | (ord($str{$i++}) << 1) | (ord($str{$i}) >> 7);
            $result[$j++] = ((ord($str{$i++}) & 0x7f) << 8) | ord($str{$i});
        }
        $result = array_reverse($result);
        $i = count($result) - 1;
        while ($result[$i] == 0) {
            array_pop($result);
            $i--;
        }
        return $result;
    }
    function bigint_num2str($num) {
        ksort($num, SORT_NUMERIC);
        $n = count($num);
        $n += 8 - ($n % 8);
        $num = array_reverse(array_pad($num, $n, 0));
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= chr($num[$i] >> 7);
            $s .= chr((($num[$i++] & 0x7f) << 1) | ($num[$i] >> 14));
            $s .= chr(($num[$i] >> 6) & 0xff);
            $s .= chr((($num[$i++] & 0x3f) << 2) | ($num[$i] >> 13));
            $s .= chr(($num[$i] >> 5) & 0xff);
            $s .= chr((($num[$i++] & 0x1f) << 3) | ($num[$i] >> 12));
            $s .= chr(($num[$i] >> 4) & 0xff);
            $s .= chr((($num[$i++] & 0x0f) << 4) | ($num[$i] >> 11));
            $s .= chr(($num[$i] >> 3) & 0xff);
            $s .= chr((($num[$i++] & 0x07) << 5) | ($num[$i] >> 10));
            $s .= chr(($num[$i] >> 2) & 0xff);
            $s .= chr((($num[$i++] & 0x03) << 6) | ($num[$i] >> 9));
            $s .= chr(($num[$i] >> 1) & 0xff);
            $s .= chr((($num[$i++] & 0x01) << 7) | ($num[$i] >> 8));
            $s .= chr($num[$i] & 0xff);
        }
        return ltrim($s, chr(0));
    }

    function bigint_random($n, $s) {
        $lowBitMasks = array(0x0000, 0x0001, 0x0003, 0x0007,
                             0x000f, 0x001f, 0x003f, 0x007f,
                             0x00ff, 0x01ff, 0x03ff, 0x07ff,
                             0x0fff, 0x1fff, 0x3fff);
        $r = $n % 15;
        $q = floor($n / 15);
        $result = array();
        for ($i = 0; $i < $q; $i++) {
            $result[$i] = mt_rand(0, 0x7fff);
        }
        if ($r != 0) {
            $result[$q] = mt_rand(0, $lowBitMasks[$r]);
            if ($s) {
                $result[$q] |= 1 << ($r - 1);
            }
        }
        else if ($s) {
            $result[$q - 1] |= 0x4000;
        }
        return $result;
    }
    function bigint_powmod($x, $y, $m) {
        $n = count($y);
        $p = array(1);
        for ($i = 0; $i < $n - 1; $i++) {
            $tmp = $y[$i];
            for ($j = 0; $j < 0xf; $j++) {
                if ($tmp & 1) $p = bigint_div(bigint_mul($p, $x), $m, 1);
                $tmp >>= 1;
                $x = bigint_div(bigint_mul($x, $x), $m, 1);
            }
        }
        $tmp = $y[$i];
        while ($tmp) {
            if ($tmp & 1) $p = bigint_div(bigint_mul($p, $x), $m, 1);
            $tmp >>= 1;
            $x = bigint_div(bigint_mul($x, $x), $m, 1);
        }
        return $p;
    }
}
?>