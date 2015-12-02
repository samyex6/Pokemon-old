<?php


class Kit {

    /*
        Author: mail@theopensource.com (01-Feb-2006 03:34)
        $array: the array you want to sort
        $by: the associative array name that is one level deep
        example: name
        $order: ASC or DESC
        $type: num or str
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

        array_multisort($$sortby, ($order === 'DESC') ? SORT_DESC : SORT_ASC, ($type === 'num') ? SORT_NUMERIC : SORT_STRING, $array);

        return $array;
    }


    public static function ArrayIconv($from, $to, &$array) {

        if(is_array($array)) {

            foreach($array as &$k) {

                self::ArrayIconv($from, $to, $k);

            }

        } else {

            $array = iconv($from, $to, $array);

        }

        return $array;

    }


    public static function JsonConvert($array) {

        //return json_encode(self::ArrayIconv('gbk', 'utf-8//IGNORE', $array));
        return json_encode($array);

    }


    public static function MultiPage($limit, $count = 0, $ulproperty = '', $tag = 'a') {

        $pagenum    = !empty($_GET['pagenum']) ? max(intval($_GET['pagenum']), 1) : 1;
        $start      = ($pagenum - 1) * $limit;
        $maxpagenum = ($count === 0) ? 9999 : ceil($count / $limit);

        $multipage = '<ul class="flt-r mp"' . ($ulproperty ? ' ' . $ulproperty : '') . '>' . (($pagenum > 1) ? '<li data-pagenum="' . max($pagenum - 1, 1) . '">&lt;&lt;</li>' : '');

        for($i = max($pagenum - 5, 1), $j = min($pagenum + 5, $maxpagenum); $i <= $j; $i++) {

            $multipage .= '<li data-pagenum="' . $i . '"' . (($i == $pagenum) ? ' class="cur"' : '') . '>' . $i . '</li>';

        }

        $multipage .= (($pagenum < $maxpagenum) ? '<li data-pagenum="' . min($pagenum + 1, $maxpagenum) . '">&gt;&gt;</li>' : '') . '</ul>';

        return [
            'start'   => $start,
            'limit'   => $limit,
            'display' => $multipage
        ];

    }


    public static function ColumnSearch($array, $column, $value) {

        if(is_array($array)) {
            foreach($array as $key => $val) {

                if($val[$column] == $value) {

                    return $key;

                }

            }
        }

        return FALSE;

    }

    public static function Library($type, $file) {

        foreach($file as $val) {

            if($type === 'class' || $type === 'db') {

                require_once ROOT . '/include/' . $type . '-' . $val . '.php';

            }

        }

        return TRUE;

    }

    public static function Memory($size) {

        $i    = 0;
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return ($size <= 0 || round($size / pow(1024, ($i = (int)floor(log($size, 1024)))), 2)) . ' ' . $unit[$i];

    }

    public static function SendMessage($title, $content, $from, $to) {

        DB::query('INSERT INTO pkm_myinbox (title, content, uid_sender, uid_receiver, time_sent) VALUES (\'' . $title . '\', \'' . $content . '\', ' . $from . ', ' . $to . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        DB::query('UPDATE pkm_trainerdata SET has_new_message = 1 WHERE uid = ' . $to);

    }

    public static function NumberFormat($num) {

        return ($num > 999999) ? round($num / 1000000) . 'm' : (($num > 999) ? round($num / 1000) . 'k' : $num);

    }

}

class App {

    public static function Initialize() {

        global $user, $system, $user_id;

        $user = $system = [];

        // Include all the required files, including databse, config data, cache and UC
        include_once ROOT . '/include/data-config.php';
        include_once ROOT . '/../bbs/uc_client/client.php';
        include_once ROOT . '/include/class-database.php';
        include_once ROOT . '/include/class-cache.php';

        // Connect to the database
        DB::connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET);

        // Check login status & set data
        $user = self::IsLoggedIn($_COOKIE['authcode']) ? $user : [];

    }

    private static function IsLoggedIn($authcode) {

        global $user;

        list($username, $password, $questionId, $answer) = uc_authcode($authcode, 'DECODE');

        if(!$username || !$password) return FALSE;

        if($questionId && $answer) $user = uc_user_login($username, $password, 0, 1, $questionId, $answer);
        else                        $user = uc_user_login($username, $password);

        // Just a side note that -1 = not existed, -2 = wrong password
        return $user[0] > 1;

    }

    public static function Login($username, $password, $questionId = 0, $answer = '') {

    }

    public static function CreditsUpdate($uid, $value, $type = 'CURRENCY', $isFixed = FALSE) {
        $field = $type === 'EXP' ? $GLOBALS['system']['exp_field'] : $GLOBALS['system']['currency_field'];
        if($isFixed)
            return DB::query('UPDATE pre_common_member_count SET ' . $field . ' = ' . $value . ' WHERE uid = ' . $uid);
        else
            return DB::query('UPDATE pre_common_member_count SET ' . $field . ' = ' . $field . ' + ' . $value . ' WHERE uid = ' . $uid);
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