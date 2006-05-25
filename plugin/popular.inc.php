<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: popular.inc.php,v 1.16.2 2006/05/25 15:28:01 miko Exp $
//
// Popular pages plugin: Show an access ranking of this wiki
// -- like recent plugin, using counter plugin's count --

/*
 * (C) 2006      PukiWiki Plus Team
 * (C) 2003-2005 PukiWiki Developers Team
 * (C) 2002 Kazunori Mizushima <kazunori@uc.netyou.jp>
 *
 * �̻�����Ӻ�����ʬ���ư������뤳�Ȥ��Ǥ��ޤ���
 *
 * [Usage]
 *   #popular
 *   #popular(20)
 *   #popular(20,FrontPage|MenuBar)
 *   #popular(20,FrontPage|MenuBar,today)
 *   #popular(20,FrontPage|MenuBar,total)
 *   #popular(20,FrontPage|MenuBar,yesterday)
 *   #popular(20,FrontPage|MenuBar,recent)
 *
 * [Arguments]
 *   1 - ɽ��������                             default 10
 *   2 - ɽ�������ʤ��ڡ���������ɽ��             default �ʤ�
 *   3 - �̻�(total)������(today)������(yesterday)���Ƕ�(recent)���Υե饰  default total
 */

define('PLUGIN_POPULAR_DEFAULT', 10);

function plugin_popular_convert()
{
	global $vars, $whatsnew;
	global $_popular_plugin_frame, $_popular_plugin_today_frame;
	global $_popular_plugin_yesterday_frame, $_popular_plugin_recent_frame;

//	$_popular_plugin_frame_s       = _('popular(%d)');
//	$_popular_plugin_today_frame_s = _('today\'s(%d)');
//	$_popular_plugin_yesterday_frame_s = _('yesterday\'s(%d)');
//	$_popular_plugin_recent_frame_s    = _('recent\'s(%d)');
//	$_popular_plugin_frame         = sprintf('<h5>%s</h5><div>%%s</div>', $_popular_plugin_frame_s);
//	$_popular_plugin_today_frame   = sprintf('<h5>%s</h5><div>%%s</div>', $_popular_plugin_today_frame_s);
//	$_popular_plugin_yesterday_frame = sprintf('<h5>%s</h5><div>%%s</div>', $_popular_plugin_yesterday_frame_s);
//	$_popular_plugin_recent_frame    = sprintf('<h5>%s</h5><div>%%s</div>', $_popular_plugin_recent_frame_s);
	$view   = 'total';
	$max    = PLUGIN_POPULAR_DEFAULT;
	$except = '';

//	list($zone, $zonetime) = set_timezone(DEFAULT_LANG);
//	$localtime = UTIME + $zonetime;
	$localtime = UTIME + date('Z');

	$today = gmdate('Y/m/d', $localtime);
	$yesterday = gmdate('Y/m/d', strtotime('yesterday', $localtime));

	$array = func_get_args();
	switch (func_num_args()) {
	case 3:
		switch ($array[2]) {
		case 'today':
		case 'true' :
			$view = 'today';
			break;
		case 'yesterday':
			$view = 'yesterday';
			break;
		case 'recent':
			$view = 'recent';
			break;
		case 'total':
		case 'false':
		default:
			$view = 'total';
			break;
		}
	case 2: $except = $array[1];
	case 1: $max    = $array[0];
	}

	$counters = array();
	foreach (get_existpages(COUNTER_DIR, '.count') as $file=>$page) {
		if (($except != '' && ereg($except, $page)) ||
		    $page == $whatsnew || check_non_list($page) ||
		    ! is_page($page))
			continue;

		$array = file(COUNTER_DIR . $file);
		$count = rtrim($array[0]);
		$date  = rtrim($array[1]);
		$today_count = rtrim($array[2]);
		$yesterday_count = rtrim($array[3]);

		$counters['_' . $page] = 0;
		if ($view == 'today' || $view == 'recent') {
			// $page�����ͤ˸�����(���Ȥ���encode('BBS')=424253)�Ȥ���
			// array_splice()�ˤ�äƥ����ͤ��ѹ�����Ƥ��ޤ��Τ��ɤ�
			// ���ᡢ������ '_' ��Ϣ�뤹��
			if ($today == $date) $counters['_' . $page] = $today_count;
		} 
		if ($view == 'yesterday' || $view == 'recent') {
			if ($today == $date) {
				$counters['_' . $page] += $yesterday_count;
			} elseif ($yesterday == $date) {
				$counters['_' . $page] += $today_count;
			}
		}
		if ($view == 'total') {
			$counters['_' . $page] = $count;
		}
	}
	asort($counters, SORT_NUMERIC);

	// BugTrack2/106: Only variables can be passed by reference from PHP 5.0.5
	$counters = array_reverse($counters, TRUE); // with array_splice()
	$counters = array_splice($counters, 0, $max);

	$items = '';
	if (! empty($counters)) {
		$items = '<ul class="popular_list">' . "\n";

		foreach ($counters as $page=>$count) {
			$page = substr($page, 1);

			$s_page = htmlspecialchars($page);
			if ($page == $vars['page']) {
				// No need to link itself, notifies where you just read
				$pg_passage = get_pg_passage($page,FALSE);
				$items .= ' <li><span title="' . $s_page . ' ' . $pg_passage . '">' .
					$s_page . '<span class="counter">(' . $count .
					')</span></span></li>' . "\n";
			} else {
				$items .= ' <li>' . make_pagelink($page,
					$s_page . '<span class="counter">(' . $count . ')</span>') .
					'</li>' . "\n";
			}
		}
		$items .= '</ul>' . "\n";
	}

	switch ($view) {
	case 'today':
		$frame = $_popular_plugin_today_frame;
		break;
	case 'yesterday':
		$frame = $_popular_plugin_yesterday_frame;
		break;
	case 'recent':
		$frame = $_popular_plugin_recent_frame;
		break;
	case 'total':
	default:
		$frame = $_popular_plugin_frame;
		break;
	}
	return sprintf($frame, count($counters), $items);
}
?>
