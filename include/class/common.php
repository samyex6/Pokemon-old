<?php


class Kit {

    /*
        Author: mail@theopensource.com (01-Feb-2006 03:34)
        $array: the array you want to sort
        $by: the associative array name that is one level deep
        example: name
        $order: ASC or DESC
        $type: quantity or str
    */

    public static function ColumnSort($array, $by, $order, $type) {

        $sortby   = 'sort' . $by;
        $firstval = current($array);
        $vals     = array_keys($firstval);

        foreach($vals as $init) {
            $keyname  = 'sort' . $init;
            $$keyname = [];
        }

        foreach($array as $key => $row) {
            foreach($vals as $names) {
                $keyname    = 'sort' . $names;
                $test       = [];
                $test[$key] = $row[$names];
                $$keyname   = array_merge($$keyname, $test);
            }
        }

        array_multisort($$sortby, ($order === 'DESC') ? SORT_DESC : SORT_ASC, ($type === 'quantity') ? SORT_NUMERIC : SORT_STRING, $array);

        return $array;
    }


    public static function JsonConvert($array) {
        return json_encode($array, defined('DEBUG_MODE') && DEBUG_MODE ? JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE : JSON_NUMERIC_CHECK);
    }

    public static function ColumnSearch($array, $column, $value) {
        if(is_array($array)) {
            foreach($array as $key => $val) {
                if($val[$column] == $value) return $key;
            }
        }
        return FALSE;
    }

    public static function Library($type, $file) {
        foreach($file as $val) {
            if($type === 'class' || $type === 'db')
                require_once ROOT . '/include/' . $type . '/' . $val . '.php';
        }
        return TRUE;
    }

    public static function Memory($size) {
        $units = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        $pow   = $size ? log($size) / log(1024) : 0;
        $size /= pow(1024, $pow);
        return round($size, 2) . ' ' . $units[(int)$pow];
    }

    public static function SendMessage($title, $content, $from, $to) {
        DB::query('INSERT INTO pkm_myinbox (title, content, uid_sender, uid_receiver, time_sent) VALUES (\'' . $title . '\', \'' . $content . '\', ' . $from . ', ' . $to . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        DB::query('UPDATE pkm_trainerdata SET has_new_message = 1 WHERE uid = ' . $to);
    }

    public static function NumberFormat($num) {
        return ($num > 999999) ? round($num / 1000000) . 'm' : (($num > 999) ? round($num / 1000) . 'k' : $num);
    }

    public static function Cutstr($string, $length, $dot = ' ...') {

        if(strlen($string) <= $length) return $string;

        $string = str_replace(['&amp;', '&quot;', '&lt;', '&gt;'], ['&', '"', '<', '>'], $string);
        $strcut = '';

        if(strtolower(UC_CHARSET) == 'utf-8') {
            $n = $tn = $noc = 0;
            while($n < strlen($string)) {
                $t = ord($string[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) $tn = 1 && ++$n && ++$noc;
                elseif(194 <= $t && $t <= 223) $tn = 2 && $n += 2 && $noc += 2;
                elseif(224 <= $t && $t < 239) $tn = 3 && $n += 3 && $noc += 2;
                elseif(240 <= $t && $t <= 247) $tn = 4 && $n += 4 && $noc += 2;
                elseif(248 <= $t && $t <= 251) $tn = 5 && $n += 5 && $noc += 2;
                elseif($t == 252 || $t == 253) $tn = 6 && $n += 6 && $noc += 2;
                else $n++;
                if($noc >= $length) break;
            }
            if($noc > $length) $n -= $tn;
            $strcut = substr($string, 0, $n);
        } else {
            for($i = 0; $i < $length; $i++)
                $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
        $strcut = str_replace(['&', '"', '<', '>'], ['&amp;', '&quot;', '&lt;', '&gt;'], $strcut);

        return $strcut . $dot;
    }

    public static function FetchFields($fields) {
        return implode(',', array_unique(explode(',', implode(',', $fields))));
    }

    /**
     * This was originally written by Andrew G. but since it was using the width and height for whole
     * image to do calculation which will cause plenty of unecessary operations so the performance was
     * terriblly slow. I optimized it by bounding the scanning pixels into a certain range, and optimized
     * for loop declaration a bit. Since intensity is not used by me so I removed related code as well.
     * The performance now increased by 90%.
     * To see changes, compare the following code with the source code in Git.
     * @author    Andrew G. Johnson <andrew@andrewgjohnson.com>, Sam Y. <pokeuniv@gmail.com>
     * @link      http://github.com/andrewgjohnson/imagettftextblur
     * @param      $image
     * @param      $size
     * @param      $angle
     * @param      $x
     * @param      $y
     * @param      $color
     * @param      $fontfile
     * @param      $text
     * @return array
     */
    public static function imagettftextblur(&$image, $size, $angle, $x, $y, $color, $fontfile, $text) {
        $text_shadow_image   = imagecreatetruecolor($image_x = imagesx($image), $image_y = imagesy($image));
        $text_box            = imagettfbbox(9, 0, $fontfile, $text);
        $text_shadow_image_x = min($x + $text_box[2] - $text_box[0] + 5, $image_x);
        $text_shadow_image_y = min($y + $text_box[3] - $text_box[1] + 5, $image_y);

        imagefill($text_shadow_image, 0, 0, imagecolorallocate($text_shadow_image, 0x00, 0x00, 0x00));
        imagettftext($text_shadow_image, $size, $angle, $x, $y, imagecolorallocate($text_shadow_image, 0xFF, 0xFF, 0xFF), $fontfile, $text);
        imagefilter($text_shadow_image, IMG_FILTER_GAUSSIAN_BLUR);

        for($x_offset = $x - 10; $x_offset < $text_shadow_image_x; $x_offset++) {
            for($y_offset = $y - 10; $y_offset < $text_shadow_image_y; $y_offset++) {
                $visibility = (imagecolorat($text_shadow_image, $x_offset, $y_offset) & 0xFF) / 255;
                if($visibility > 0)
                    imagesetpixel($image, $x_offset, $y_offset, imagecolorallocatealpha($image, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF, (1 - $visibility) * 127));
            }
        }
        imagedestroy($text_shadow_image);
    }

}

class App {

    public static function Initialize() {

        global $user, $system, $lang, $start_time;

        $user       = $system = $lang = [];
        $start_time = microtime(TRUE);

        // Include all the required files, including databse, config data, cache and UC
        include_once ROOT . '/include/language/' . LANGUAGE . '.php';
        include_once ROOT . '/include/data/config.php';
        include_once ROOT . '/../bbs/uc_client/client.php';
        include_once ROOT . '/include/class/database.php';
        include_once ROOT . '/include/class/cache.php';
        include_once ROOT . '/include/constant/common.php';

        // Connect to the database
        DB::connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET);

        // Check login status & set data
        if(!self::IsLoggedIn($_COOKIE['authcode'])) $user = ['uid' => 0];

    }

    private static function IsLoggedIn($authcode) {

        global $user;
        list($username, $password, $questionId, $answer) = explode(',,', uc_authcode($authcode, 'DECODE'));

        if(!$username || !$password) return FALSE;

        list($user['uid'], $user['username'], , $user['email']) = uc_user_login($username, $password, 0, $questionId && $answer, $questionId, $answer);

        // Just a side note that -1 = not existed, -2 = wrong password
        return $user['uid'] > 0;

    }

    public static function Login($username, $password, $questionId = 0, $answer = '') {
        global $user, $synclogin;
        list($user['uid'], $user['username'], , $user['email']) = uc_user_login($username, $password, 0, $questionId && $answer, $questionId, $answer);
        if($user['uid'] <= 0) return FALSE;
        $synclogin = uc_user_synlogin($user['uid']);
        setcookie('authcode', uc_authcode($username . ',,' . $password . ',,' . $questionId . ',,' . $answer, 'ENCODE'), $_SERVER['REQUEST_TIME'] + 99999999);
        return TRUE;
    }

    public static function CreditsUpdate($uid, $value, $type = 'CURRENCY', $isFixed = FALSE) {
        global $system, $trainer;
        if($type === 'EXP') {
            $field          = $system['exp_field'];
            $trainer['exp'] = $isFixed ? $value : $trainer['exp'] + $value;
        } else {
            $field               = $system['currency_field'];
            $trainer['currency'] = $isFixed ? $value : $trainer['currency'] + $value;
        }
        if($isFixed) return DB::query('UPDATE pre_common_member_count SET `' . $field . '` = ' . $value . ' WHERE uid = ' . $uid);
        else return DB::query('UPDATE pre_common_member_count SET `' . $field . '` = ' . $field . ' + ' . $value . ' WHERE uid = ' . $uid);
    }

    private function GetUserIp() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach($matches[0] AS $xip) {
                if(!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

}