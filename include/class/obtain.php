<?php

class Obtain {

    private static $hex = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
    private static $box = [];
    private static $resources = [];

    public static function MeetPlace($mtplace) {

        $mapname = DB::result_first('SELECT name_zh name FROM pkm_mapdata WHERE map_id = ' . $mtplace);

        if(!empty($mapname)) {
            return '在' . $mapname . '遇见。';
        } else {
            require ROOT . '/include/data/birthplace.php';
            return isset($birthplace[$mtplace]) ? $birthplace[$mtplace] : '……从石头里蹦出来的？';
        }

    }

    /*
        ObtainDepositBox
            Global variable: $system, $user
            First get the maximum box possible by using the formula: 
                user's boxes + initial boxes + 100
            Which get a number greater than or equal to 100
            Then obtain the amount of pokemon in each boxes and party the trainer have
    */

    public static function DepositBox($uid) {

        global $system, $trainer;

        $maxboxnum = $trainer['box_quantity'] + $system['initial_box'] + 100;
        self::$box = [];

        if(empty(self::$box)) {
            $query = DB::query('SELECT location, COUNT(*) total FROM pkm_mypkm WHERE uid = ' . $uid . ' AND (location IN (1, 2, 3, 4, 5, 6) OR location > 100) GROUP BY location');
            while($pokemon = DB::fetch($query))
                self::$box[$pokemon['location']] = $pokemon['total'];
        }
        for($i = 1; $i <= $maxboxnum; $i++) {
            if(empty(self::$box[$i]) || $i > 100 && self::$box[$i] < $system['pkm_per_box']) {
                self::$box[$i] = isset(self::$box[$i]) ? self::$box[$i] + 1 : 1;
                return $i;
            }
            if($i === 6) $i = 100;
        }

        return FALSE;

    }

    public static function TypeName($type, $typeb = 0, $image = FALSE, $appendclass = '') {
        $typearr = $GLOBALS['lang']['data_move_types'];
        if(!$image) $result = !empty($typearr[$type]) ? $typearr[$type] . ($typeb > 0 && !empty($typearr[$typeb]) ? '+' . $typearr[$typeb] : '') : '';
        else        $result = !empty($typearr[$type]) ? '<span class="type t' . $type . $appendclass . '"></span>' . ($typeb > 0 && !empty($typearr[$typeb]) ? '&nbsp;&nbsp;<span class="type t' . $typeb . $appendclass . '"></span>' : '') : '';
        return $result;

    }

    public static function MoveClassName($class) {
        $classarr = $GLOBALS['lang']['data_move_classes'];
        return ($classarr[$class]) ? $classarr[$class] : self::Text('unknown');
    }

    public static function EggGroupName($group, $groupb = 0) {
        $grouparr = $GLOBALS['lang']['data_egg_groups'];
        $result   = !empty($grouparr[$group]) ? $grouparr[$group] . ($groupb > 0 && !empty($grouparr[$groupb]) ? '+' . $grouparr[$groupb] : '') : '';
        return $result;
    }

    public static function ItemClassName($class) {
        $classarr = $GLOBALS['lang']['data_item_types'];
        return ($classarr[$class]) ? $classarr[$class] : self::Text('unknown');
    }

    public static function GenderSign($gender) {
        $genderarr = ['', '<span class=gender-m>♂</span>', '<span class=gender-f>♀</span>'];
        return $genderarr[$gender];
    }

    public static function Devolution($id) {

        $da = DB::result_first('SELECT devolution FROM pkm_pkmextra WHERE nat_id = ' . $id);

        if(!empty($da)) {
            $db = DB::result_first('SELECT devolution FROM pkm_pkmextra WHERE nat_id = ' . $da);
            return (!empty($db) ? $db : $da);
        } else {
            return $id;
        }

    }

    public static function StatusIcon($status) {

        if(!$status) return '<font class=status></font>';

        $statusarr = [
            1 => ['red', '烧'],
            2 => ['lightblue', '冻'],
            3 => ['orange', '痹'],
            4 => ['blue', '眠'],
            5 => ['purple', '毒'],
            6 => ['purple', '剧']
        ];

        return '<font class=status color=' . $statusarr[$status][0] . '>' . $statusarr[$status][1] . '</font>';

    }

    public static function Sprite($class, $type, $filename, $refresh = FALSE, $side = 0) {

        $filenameh = base_convert(hash('joaat', $filename . ($side === 1 ? '_b' : '')), 16, 32);
        $path      = ROOT_CACHE . '/image/' . $filenameh . '.' . $type;

        if(file_exists($path) && $refresh === FALSE) return $path;

        $data = explode('_', $filename);

        switch($class) {
            case 'pokemon':

                if(count($data) < 5) {

                    /*
                     * Invalid parameters
                     *  Shown as the Bug pokemon in games
                     */

                    return ROOT_CACHE . '/image/_unknownpokemon.png';

                } elseif($data[1] == 327111 && $side === 0) {

                    /*
                        This is for spinda front sprite only
                        Do some spot's placement calculation and special layers to generate
                    */

                    $pv = [];

                    for($i = 0; $i < 8; $i++) {
                        $pv[$i] = ('0x' . $data[5]{$i}) * 1;
                    }

                    $spot = [
                        [$pv[7], $pv[6]],
                        [$pv[5] + 24, $pv[4] + 2],
                        [$pv[3] + 3, $pv[2] + 16],
                        [$pv[1] + 15, $pv[0] + 18]
                    ];

                    $extrapath = ($data[4] == 1) ? '-shiny' : '';

                    $img  = imagecreatefrompng(ROOT_IMAGE . '/pokemon/front' . $extrapath . '/327.' . $type);
                    $imgb = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_1.png');
                    $imgc = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_2.png');
                    $imgd = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_3.png');
                    $imge = imagecreatefrompng(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_spot_4.png');
                    $imgf = imagecreatefromgif(ROOT_IMAGE . '/merge/spinda_' . $extrapath . '_overlap.gif');

                    imagecopymerge($img, $imgb, $spot[0][0] + 23, $spot[0][1] + 15, 0, 0, 8, 8, 80);
                    imagecopymerge($img, $imgc, $spot[1][0] + 23, $spot[1][1] + 15, 0, 0, 8, 8, 80);
                    imagecopymerge($img, $imgd, $spot[2][0] + 23, $spot[2][1] + 15, 0, 0, 7, 9, 80);
                    imagecopymerge($img, $imge, $spot[3][0] + 23, $spot[3][1] + 15, 0, 0, 9, 10, 80);
                    imagecopymerge($img, $imgf, 0, 0, 0, 0, 96, 96, 100);

                    $translayer = imagecreatetruecolor(96, 96);
                    $trans      = imagecolorallocate($translayer, 255, 255, 255);

                    imagecolortransparent($translayer, $trans);
                    imagecopy($translayer, $img, 0, 0, 0, 0, 96, 96);
                    imagetruecolortopalette($translayer, TRUE, 256);
                    imageinterlace($translayer);

                    $img = $translayer;

                } else {

                    $extrapath = (($side === 1) ? '/back' : '/front') .
                        (($data[4] == 1) ? '-shiny' : '') .
                        (($data[2] == 1) ? '/female' : '') .
                        (($data[3] > 0) ? '/' . $data[1] . '-' . $data[3] : '/' . $data[1] . '.') .
                        (($type === 'jpeg') ? 'jpg' : $type);

                    copy(ROOT_IMAGE . '/pokemon' . $extrapath, $path);

                    return $path;
                }

                /*
                    [Currently unavailable]
                    Gray filter for the dead pokemon
                    if($data['hp'] == 0) {
                        //imagefilter($img, IMG_FILTER_GRAYSCALE);
                        imagecopymergegray($img, $img, 0, 0, 0, 0, 96, 96, 0);
                    }
                */

                break;
            case 'item':

                if(!file_exists(ROOT_IMAGE . '/item/' . $data[1] . '.' . $type))
                    return ROOT_CACHE . '/image/_unknownitem.png';

                $img        = imagecreatefrompng(ROOT_IMAGE . '/item/' . $data[1] . '.' . $type);
                $translayer = imagecreate(24, 24);
                $trans      = imagecolorallocate($translayer, 255, 255, 255);

                imagecolortransparent($translayer, $trans);
                imagecopy($translayer, $img, 0, 0, 0, 0, 24, 24);
                imagetruecolortopalette($translayer, TRUE, 256);
                imageinterlace($translayer);

                $img = $translayer;

                break;
            case 'other':

                // Other sprites such as hp bar or exp bar, maybe more in the future

                if(in_array($data[0], ['hp', 'exp'])) {
                    $img  = imagecreatefromgif(ROOT_IMAGE . '/other/' . $data[0] . '_border.' . $type);
                    $imgb = imagecreatefromgif(ROOT_IMAGE . '/other/' . $data[0] . '_fill.' . $type);
                    imagecopy($img, $imgb, 1, 1, 0, 0, $data[2], 4);
                } else {
                    $head = 'imagecreatefrom' . $type;
                    $img  = $head(ROOT_IMAGE . '/other/' . $data[0] . '.' . $type);
                }

                break;
            case 'egg':
                $img = imagecreatefrompng(ROOT_IMAGE . '/pokemon/0.' . $type);
                break;
            case 'pokemon-icon':

                $img        = imagecreatefrompng(ROOT_IMAGE . '/pokemon-icon/' . $data[1] . '.' . $type);
                $translayer = imagecreate(32, 32);
                $trans      = imagecolorallocate($translayer, 255, 255, 255);

                imagecolortransparent($translayer, $trans);
                imagecopy($translayer, $img, 0, 0, 0, 0, 32, 32);
                imagetruecolortopalette($translayer, TRUE, 256);
                imageinterlace($translayer);

                $img = $translayer;

                break;
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);
        $content = ob_get_contents();
        ob_clean();
        $handle = fopen($path, 'w+');
        fwrite($handle, $content);
        fclose($handle);
        return $path;

    }

    /*
        Exp
        - 60 type (n <= 50, 50 <= n <= 68, 68 < n < 98, 98 <= n <= 100)
        - 80, 100, 105, 125 type (All)
        - 164 type (n < 15, 15 <= n <= 36, 36 <= n <= 100)
    */

    public static function Exp($exptype, $nextlevel) {
        if($nextlevel - 1 <= 0) return 0;
        switch($exptype) {
            case 1:
                if($nextlevel <= 50) $nextexp = pow($nextlevel, 3) * (100 - $nextlevel) / 50;
                elseif($nextlevel > 50 && $nextlevel <= 68) $nextexp = pow($nextlevel, 3) * (150 - $nextlevel) / 100;
                elseif($nextlevel > 68 && $nextlevel < 98) $nextexp = pow($nextlevel, 3) * (1911 - 10 * $nextlevel) / 1500;
                else                                            $nextexp = floor(pow($nextlevel, 3) * (160 - $nextlevel) / 100);
                break;
            case 2:
                $nextexp = 0.8 * pow($nextlevel, 3);
                break;
            case 3:
                $nextexp = pow($nextlevel, 3);
                break;
            case 4:
                $nextexp = 1.2 * pow($nextlevel, 3) - 15 * pow($nextlevel, 2) + 100 * $nextlevel - 140;
                break;
            case 5:
                $nextexp = 1.25 * pow($nextlevel, 3);
                break;
            case 6:
                if($nextlevel < 15) $nextexp = pow($nextlevel, 3) * ($nextlevel + 73) / 150;
                elseif($nextlevel >= 15 && $nextlevel <= 36) $nextexp = pow($nextlevel, 3) * ($nextlevel + 14) / 50;
                else                                            $nextexp = pow($nextlevel, 3) * ($nextlevel + 64) / 100;
                break;
        }
        if($nextexp < 0) $nextexp = 0;
        return floor($nextexp);
    }

    public static function NatureName($nature) {

        $naturearr = ['努力', '寂寞', '勇敢', '固执', '顽皮',
                      '大胆', '坦率', '悠闲', '淘气', '无虑',
                      '胆小', '急躁', '认真', '开朗', '天真',
                      '谨慎', '温和', '冷静', '腼腆', '马虎',
                      '安静', '温顺', '傲慢', '慎重', '浮躁'];

        return $naturearr[$nature - 1];

    }

    public static function Stat($level, $bs, $iv, $ev, $nature = 1, $hp = TRUE) {

        $bs       = explode(',', $bs);
        $iv       = explode(',', $iv);
        $ev       = explode(',', $ev);
        $modifier = self::NatureModifier($nature);

        $prefix = ['max_hp', 'atk', 'def', 'spatk', 'spdef', 'spd'];
        foreach($prefix as $key => $val) {
            switch($key) {
                default:
                    $result[$val] = floor(floor(floor($bs[$key] * 2 + $ev[$key] / 4 + $iv[$key]) * $level / 100 + 5) * $modifier[$key]);
                    break;
                case 0:
                    $result['max_hp'] = ($bs[$key] != 1) ? floor(floor($bs[$key] * 2 + $ev[$key] / 4 + $iv[$key]) * $level / 100 + $level + 10) : 1;
                    break;
            }
        }

        if($hp !== FALSE) $result['hp_percent'] = min(ceil($hp / $result['max_hp'] * 100), 100);

        return $result;
    }

    public static function NatureModifier($nature) {
        $result = [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1];
        if(($nature - 1) % 6 !== 0) {
            $checkstr                           = '00121314152100232425313200343541424300455152535400';
            $result[$checkstr{$nature * 2 - 2}] = 1.1;
            $result[$checkstr{$nature * 2 - 1}] = 0.9;
        }
        return $result;
    }

    public static function BagItem($condition = '', $orderby = '', $mode = '') {

        global $trainer;

        $condition = ($condition !== '') ? ' AND ' . $condition : '';
        $orderby   = ($orderby !== '') ? ' ORDER BY ' . $orderby : '';
        $mode      = ($mode !== '') ? explode(':', $mode) : '';
        $query     = DB::query('SELECT mi.item_id, mi.quantity, i.name_zh name, i.description, i.type
                                 FROM pkm_myitem mi
                                 LEFT JOIN pkm_itemdata i ON i.item_id = mi.item_id
                                 WHERE mi.uid = ' . $trainer['uid'] . $condition . $orderby);
        $item      = [];
        while($info = DB::fetch($query)) {
            if($mode !== '') $item[$info[$mode[1]]][] = $info;
            else                $item[] = $info;
        }
        return $item;
    }

    public static function TrainerAvatar($uid, $size = 'middle') {

        $uid  = sprintf("%09d", abs(intval($uid)));
        $path = '../bbs/uc_server/data/avatar/' . substr($uid, 0, 3) . '/' .
            substr($uid, 3, 2) . '/' .
            substr($uid, 5, 2) . '/' .
            substr($uid, -2) . '_avatar_' . (in_array($size, ['big', 'middle', 'small']) ? $size : 'middle') . '.jpg';

        return (file_exists($path) ? $path : '../bbs/uc_server/images/noavatar_' . $size . '.gif');

    }

    public static function Avatar($uid, $refresh = FALSE) {

        $filenameh = base_convert(hash('joaat', $uid), 16, 32);
        $path      = ROOT_CACHE . '/avatar/' . $filenameh . '.png';

        if(file_exists($path) && $refresh === FALSE) return $path;

        $file  = glob(ROOT_IMAGE . '/avatar-part/skin*');
        $fileb = glob(ROOT_IMAGE . '/avatar-part/eye*');
        $filec = glob(ROOT_IMAGE . '/avatar-part/cos*');
        $filed = glob(ROOT_IMAGE . '/avatar-part/hair*');
        $filee = glob(ROOT_IMAGE . '/avatar-part/bangs*');
        $filef = glob(ROOT_IMAGE . '/avatar-part/hat*');
        $fileg = glob(ROOT_IMAGE . '/avatar-part/dec*');

        $img  = imagecreatefrompng($file[array_rand($file)]);
        $imgb = imagecreatefrompng($fileb[array_rand($fileb)]);
        $imgc = imagecreatefrompng($filec[array_rand($filec)]);
        $imgd = imagecreatefrompng($filed[array_rand($filed)]);
        $imge = imagecreatefrompng($filee[array_rand($filee)]);
        $imgf = imagecreatefrompng($filef[array_rand($filef)]);
        $imgg = imagecreatefrompng($fileg[array_rand($fileg)]);

        imagecopy($img, $imgb, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgc, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgd, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imge, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgf, 0, 0, 0, 0, 40, 40);
        imagecopy($img, $imgg, 0, 0, 0, 0, 40, 40);

        $translayer = imagecreate(40, 40);
        $trans      = imagecolorallocate($translayer, 255, 255, 255);

        imagecolortransparent($translayer, $trans);
        imagecopy($translayer, $img, 0, 0, 0, 0, 40, 40);
        imagetruecolortopalette($translayer, TRUE, 256);
        imageinterlace($translayer);

        $img = $translayer;

        ob_start();
        imagepng($img);
        imagedestroy($img);
        $content = ob_get_contents();
        ob_clean();
        $handle = fopen($path, 'w+');
        fwrite($handle, $content);
        fclose($handle);
        return $path;

    }

    /**
     * Fetch a certain text from the language pack.
     * If it's not a dataset and it's an array, then randomize one.
     * @param string $key name of the text
     * @param array $args arguments that will be replacing placeholders using vsprintf
     * @param bool $is_data if is a dataset, then it doesn't need to check if it's an array or not
     * @param bool $add_linebreak
     * @return string
     */
    public static function Text($key, $args = [], $is_data = FALSE, $add_linebreak = FALSE, $data_index = 0) {

        if(empty($GLOBALS['lang'][$key])) return $GLOBALS['lang']['inoccupied_text'];

        $text = $GLOBALS['lang'][$key];
        if($is_data) $text = $text[$data_index];
        if(!$is_data && is_array($text)) $text = $text[array_rand($text)];
        if($args) $text = vsprintf($text, $args);

        return $text . ($add_linebreak ? '<br>' : '');

    }

    public static function DaycareInfo($sent_time) {
        return [
            'cost'          => (floor(($_SERVER['REQUEST_TIME'] - $sent_time) / 3600) + 1) * 10,
            'exp_increased' => floor(($_SERVER['REQUEST_TIME'] - $sent_time) / 12)
        ];
    }

    public static function HatchTime($egg_cycle) {
        return $_SERVER['REQUEST_TIME'] + floor($egg_cycle * 255 * (rand(0, 5) + $egg_cycle * 0.6) / 6);
    }

    public static function TrainerCard($trainer, $force_refresh = FALSE) {

        global $lang;

        $path = ROOT_CACHE . '/image/trainer-card/' . base_convert(hash('joaat', $trainer['uid']), 16, 32) . '.png';

        if(!$force_refresh && file_exists($path) && filemtime($path) + 600 > $_SERVER['REQUEST_TIME']) return $path;

        $background_resource = imagecreatefromjpeg(ROOT_IMAGE . '/trainer-card/background-1.jpg');
        $avatar_resource     = imagecreatefrompng($trainer['avatar']);
        if(empty(self::$resources['pokemon_icon']))
            self::$resources['pokemon_icon'] = imagecreatefrompng(ROOT_IMAGE . '/pokemon-icon/sheet-32x32.png');
        if(empty(self::$resources['egg_icon']))
            self::$resources['egg_icon'] = imagecreatefrompng(ROOT_IMAGE . '/pokemon-icon/0.png');

        imagecopy($background_resource, $avatar_resource, 45, 10, 0, 0, 40, 40);

        $font_path       = ROOT . '/include/font/yahei-bold.ttf';
        $trainer['rank'] = '#' . $trainer['rank'];
        $text_boxes      = [
            imagettfbbox(9, 0, $font_path, $trainer['username']),
            imagettfbbox(9, 0, $font_path, $trainer['rank']),
            imagettfbbox(9, 0, $font_path, $trainer['level']),
            imagettfbbox(9, 0, $font_path, $trainer['dex_collected']),
            imagettfbbox(9, 0, $font_path, 0),
            imagettfbbox(9, 0, $font_path, $lang['rank']),
            imagettfbbox(9, 0, $font_path, $lang['level']),
            imagettfbbox(9, 0, $font_path, $lang['pokedex']),
            imagettfbbox(9, 0, $font_path, $lang['achievement']),
        ];
        $left_offsets    = [
            (130 - $text_boxes[0][2] + $text_boxes[0][0]) / 2,
            160 + ($text_boxes[5][2] - $text_boxes[5][0] - $text_boxes[1][2] + $text_boxes[1][0]) / 2,
            220 + ($text_boxes[6][2] - $text_boxes[6][0] - $text_boxes[2][2] + $text_boxes[2][0]) / 2,
            280 + ($text_boxes[7][2] - $text_boxes[7][0] - $text_boxes[3][2] + $text_boxes[3][0]) / 2,
            340 + ($text_boxes[8][2] - $text_boxes[8][0] - $text_boxes[4][2] + $text_boxes[4][0]) / 2,
        ];

        // TODO: achievement count
        Kit::imagettftextblur($background_resource, 9, 0, 161, 51, 0x000000, $font_path, $lang['rank']);
        Kit::imagettftextblur($background_resource, 9, 0, 221, 51, 0x000000, $font_path, $lang['level']);
        Kit::imagettftextblur($background_resource, 9, 0, 281, 51, 0x000000, $font_path, $lang['pokedex']);
        Kit::imagettftextblur($background_resource, 9, 0, 341, 51, 0x000000, $font_path, $lang['achievement']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[0] + 1, 84, 0x000000, $font_path, $trainer['username']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[1] + 1, 31, 0x000000, $font_path, $trainer['rank']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[2] + 1, 31, 0x000000, $font_path, $trainer['level']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[3] + 1, 31, 0x000000, $font_path, $trainer['dex_collected']);
        Kit::imagettftextblur($background_resource, 9, 0, $left_offsets[4] + 1, 31, 0x000000, $font_path, 0);
        imagettftext($background_resource, 9, 0, 160, 50, 0xFFFFFF, $font_path, $lang['rank']);
        imagettftext($background_resource, 9, 0, 220, 50, 0xFFFFFF, $font_path, $lang['level']);
        imagettftext($background_resource, 9, 0, 280, 50, 0xFFFFFF, $font_path, $lang['pokedex']);
        imagettftext($background_resource, 9, 0, 340, 50, 0xFFFFFF, $font_path, $lang['achievement']);
        imagettftext($background_resource, 9, 0, $left_offsets[0], 83, 0xFFFFFF, $font_path, $trainer['username']);
        imagettftext($background_resource, 9, 0, $left_offsets[1], 30, 0xFFFFFF, $font_path, $trainer['rank']);
        imagettftext($background_resource, 9, 0, $left_offsets[2], 30, 0xFFFFFF, $font_path, $trainer['level']);
        imagettftext($background_resource, 9, 0, $left_offsets[3], 30, 0xFFFFFF, $font_path, $trainer['dex_collected']);
        imagettftext($background_resource, 9, 0, $left_offsets[4], 30, 0xFFFFFF, $font_path, 0);

        $query = DB::query('SELECT nat_id FROM pkm_mypkm WHERE uid = ' . $trainer['uid'] . ' AND location IN (' . LOCATION_PARTY . ')');
        $i     = 0;
        while($info = DB::fetch($query)) {
            if(!$info['nat_id']) imagecopy($background_resource, self::$resources['egg_icon'], 160 + $i * 36, 63, 0, 0, 32, 32);
            else imagecopy($background_resource, self::$resources['pokemon_icon'], 160 + $i * 36, 63, ($info['nat_id'] % 12) * 32, floor($info['nat_id'] / 12) * 32, 32, 32);
            ++$i;
        }

        ob_start();
        imagepng($background_resource);
        imagedestroy($background_resource);
        imagedestroy($avatar_resource);
        $content = ob_get_contents();
        ob_clean();
        $handle = fopen($path, 'w+');
        fwrite($handle, $content);
        fclose($handle);

        return $path;
    }

    public static function HealTime($max_hp, $hp) {
        return ceil(($max_hp - $hp) * 6.6);
    }

    public static function HealRemainTime($time_pc_sent, $max_hp, $hp) {
        return max(0, $time_pc_sent + self::HealTime($max_hp, $hp) - $_SERVER['REQUEST_TIME']);
    }


}