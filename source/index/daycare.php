<?php

//1分钟5经验，需要付的货币为(向下取整(小时 / 6) + 1) * 5，递增。
/**
 * Place
 * 1-6:身上
 * 7-饲养院
 * 8-PC恢复
 * 9-丢弃
 * 10-交换
 * 101-200:箱子
 * 想法
 * - 自由随机交配
 * - 和固定朋友交配
 * - 只允许有三个配偶，配偶不准是自己的兄弟姐妹爸爸妈妈以及更老的一代（在蛋生出来后才确定）
 * - 情人节派发V蛋
 * - 圣诞节派发C蛋
 * - 万圣节派发H蛋
 */

Kit::Library('class', ['obtain']);

# Note that perhaps I will add Exp adding progress

/*
	First extract data from the database, time_daycare_sent which records the time the pokemon had sent into daycare, modifies when take the pokemon out
	time_egg_checked records the timestamp of last time being checked if there is an time_hatched or not, only modify when starts to check is it any eggs produced
*/

$query   = DB::query('SELECT m.pkm_id, m.level, m.nickname, m.nat_id, m.time_daycare_sent, m.time_egg_checked, m.time_hatched, m.gender, m.uid_initial, m.sprite_name, m.item_carrying, m.item_captured, p.egg_group, p.egg_group_b, p.name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id WHERE location = 7 AND uid = ' . $trainer['uid'] . ' LIMIT 2');
$pokemon = [];

while($info = DB::fetch($query)) {

	$info['incexp']      = floor(($_SERVER['REQUEST_TIME'] - $info['time_daycare_sent']) / 12);
	$info['cost']        = (floor(($_SERVER['REQUEST_TIME'] - $info['time_daycare_sent']) / 2400) + 1) * 5;
	$info['egg_groupn']     = Obtain::EggGroupName($info['egg_group'], $info['egg_group_b']);
	$info['pkmimgpath']  = Obtain::Sprite('pokemon', 'png', $info['sprite_name']);
	$info['gendersign']  = Obtain::GenderSign($info['gender']);
	$info['item_captured']     = Obtain::Sprite('item', 'png', 'item_' . $info['item_captured']);
	$info['itemimgpath'] = ($info['item_carrying']) ? Obtain::Sprite('item', 'png', 'item_' . $info['item_carrying']) : '';

	$pokemon[] = $info;

}

$pmcount = count($pokemon);

if($pmcount === 2) {

	$randmax = 0;

	/*
			Egg out limitation processes (By order)
			- NOT in 'undiscovered' group
				- Pokemon are Ditto and others
				- Different gender
					- Egg groups between two pokemon are match
			If one of the above happend, variable $eggpossible become true, 
			so that pokemon will get a chance of getting time_hatched
		*/

	$eggpossible = 0;

	if(!in_array(15, [$pokemon[0]['egg_group'], $pokemon[1]['egg_group']])) {

		if(in_array(132, [$pokemon[0]['nat_id'], $pokemon[1]['nat_id']])) {

			$eggpossible = 1;

		} elseif($pokemon[0]['gender'] != $pokemon[1]['gender'] && !in_array(0, [$pokemon[0]['gender'], $pokemon[1]['gender']])) {

			if(in_array($pokemon[0]['egg_group'], [$pokemon[1]['egg_group'], $pokemon[1]['egg_group_b']]) || !empty($pokemon[0]['egg_group_b']) && in_array($pokemon[0]['egg_group_b'], [$pokemon[1]['egg_group'], $pokemon[1]['egg_group_b']]))

				$eggpossible = 1;

		}

	}

	if($eggpossible === 1) {

		if($pokemon[0]['nat_id'] === $pokemon[1]['nat_id']) {

			if($pokemon[0]['uid_initial'] === $pokemon[1]['uid_initial']) {

				$randmax   = 50;
				$psbstatus = '两只精灵的感情还行。';

			} elseif($pokemon[0]['uid_initial'] != $pokemon[1]['uid_initial']) {

				$randmax   = 70;
				$psbstatus = '两只精灵的感情不错啊！';

			}

		} else {

			if($pokemon[0]['uid_initial'] === $pokemon[1]['uid_initial']) {

				$randmax   = 20;
				$psbstatus = '两只精灵的感情勉强说得过去吧……';

			} elseif($pokemon[0]['uid_initial'] != $pokemon[1]['uid_initial']) {

				$randmax   = 50;
				$psbstatus = '两只精灵的感情还行。';

			}

		}
	}

	if($pokemon[0]['time_hatched'] + $pokemon[1]['time_hatched'] === 2) {

		$randmax   = 70;
		$psbstatus = '这一对异性恋进行了一番巫山云雨，最终产下了悲剧的结晶！';

	} else {

		// If $eggpossible is 1, then start to get the relationship status, at the same time, define the max number for the random number

		if($eggpossible === 1) {

			/*
				If two pokemon haven't got an time_hatched, add a record for the time_hatched which is produced
				It is enough of using one of theirs time_egg_checked and time_daycare_sent
				$chktime is a variable which define as the loop times for using $randmax to check if any time_hatched is coming
				$chktimen is the leftover time for time used in loops, then subtracted with the timestamp for next time checking
				$hour is how many hours do a check
				Do loops for $chktime times, each time generates a random number between 0 and 100, 
				if the number is less or equal to the limitation variable $randmax, so an time_hatched has been produced
			*/

			# Note that it might be better to create another table of saving records of eggs rather than merge with the table pkm_mypkm

			if(empty($pokemon[0]['time_hatched']) || empty($pokemon[1]['time_hatched'])) {

				$hour    = 2;                // two hours
				$stamp   = $hour * 60 * 60;    // change hours into seconds
				$chktime = !empty($pokemon[0]['time_egg_checked']) ? floor(($_SERVER['REQUEST_TIME'] - $pokemon[0]['time_egg_checked']) / $stamp) : floor(($_SERVER['REQUEST_TIME'] - $pokemon[0]['time_daycare_sent']) / $stamp); //每两小时加一次循环，计算循环次数，一次性计算是否生出了蛋，计算完毕后马上更新检查时间

				if($chktime > 0) {

					$chktimen = $_SERVER['REQUEST_TIME']; // Wait for further consideration. $chktimen = $_SERVER['REQUEST_TIME'] - fmod(($_SERVER['REQUEST_TIME'] - $pokemon[0]['time_egg_checked']), $stamp);

					DB::query('UPDATE pkm_mypkm SET time_egg_checked = ' . $chktimen . ' WHERE pkm_id IN (' . $pokemon[0]['pkm_id'] . ', ' . $pokemon[1]['pkm_id'] . ')');

				}

				for($i = 0; $i < $chktime; $i++) {

					if(rand(0, 100) <= $randmax) {

						$pokemon[0]['time_hatched'] = $pokemon[1]['time_hatched'] = 1;

						DB::query('UPDATE pkm_mypkm SET time_hatched = 1 WHERE pkm_id IN (' . $pokemon[0]['pkm_id'] . ', ' . $pokemon[1]['pkm_id'] . ')');

						break;

					}

				}

			}

		} elseif($eggpossible === 0) {

			$psbstatus = '嘛、两只精灵似乎做朋友更合适点？';

		}

	}

	$egg    = ($pokemon[0]['time_hatched'] + $pokemon[1]['time_hatched'] === 2) ? 1 : 0;
	$status = '精灵一切安好。';

	if($egg === 1) {

		$eggsprite = Obtain::Sprite('egg', 'png', '');

	}


} elseif($pmcount === 1) {

	$status = '精灵一切安好。';

}


// If the spot of daycare is not full, get data of pokemon in party and display

if($pmcount < 2) {

	$pokemon = array_merge($pokemon, array_fill($pmcount, 2 - $pmcount, []));

	$query = DB::query('SELECT m.nat_id, m.nickname, m.pkm_id, m.sprite_name, m.level, m.gender, p.egg_group, p.egg_group_b, p.name FROM pkm_mypkm m LEFT JOIN pkm_pkmdata p ON m.nat_id = p.nat_id AND m.nat_id != 0 WHERE location IN (1, 2, 3, 4, 5, 6) AND uid = ' . $trainer['uid'] . ' LIMIT 6');
	$party = [];

	while($info = DB::fetch($query)) {

		$info['egg_group']     = Obtain::EggGroupName($info['egg_group'], $info['egg_group_b']);
		$info['pkmimgpath'] = Obtain::Sprite('pokemon', 'png', $info['sprite_name']);
		$info['gender']     = Obtain::GenderSign($info['gender']);

		$party[] = $info;

	}

}

?>