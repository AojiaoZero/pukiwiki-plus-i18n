<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: pcomment.inc.php,v 1.35 2004/12/02 11:34:25 henoheno Exp $
//

/*

*�ץ饰���� pcomment
���ꤷ���ڡ����˥����Ȥ�����

*Usage
 #pcomment([�ڡ���̾][,ɽ�����륳���ȿ�][,���ץ����])

*�ѥ�᡼��
-�ڡ���̾~
 ��Ƥ��줿�����Ȥ�Ͽ����ڡ�����̾��
-ɽ�����륳���ȿ�~
 ���Υ����Ȥ򲿷�ɽ�����뤫(0������)

*���ץ����
-above~
 �����Ȥ�ե�����ɤ�����ɽ��(��������������)
-below~
 �����Ȥ�ե�����ɤθ��ɽ��(��������������)
-reply~
 2��٥�ޤǤΥ����Ȥ˥�ץ饤��Ĥ���radio�ܥ����ɽ��

*/

// �ڡ���̾�Υǥե����(%s��$vars['page']������)
define('PCMT_PAGE', '[[������/%s]]');

// ɽ�����륳���ȿ��Υǥե����
define('PCMT_NUM_COMMENTS', 10);

// �����Ȥ�̾���ƥ����ȥ��ꥢ�Υ�����
define('PCMT_COLS_NAME', 15);

// �����ȤΥƥ����ȥ��ꥢ�Υ�����
define('PCMT_COLS_COMMENT', 70);

// ����������� 1:���� 0:��Ƭ
define('PCMT_INSERT_INS', 1);

// �����Ȥ������ե����ޥå�
// \x08�ϡ���Ƥ��줿ʸ������˸���ʤ�ʸ���Ǥ���Фʤ�Ǥ⤤����
define('PCMT_NAME_FORMAT',	'[[$name]]');
define('PCMT_MSG_FORMAT',	'$msg');
define('PCMT_NOW_FORMAT',	'&new{$now};');
define('PCMT_FORMAT',	"\x08MSG\x08 -- \x08NAME\x08 \x08DATE\x08");

// ��ư�������� 1�ڡ���������η������� 0��̵��
define('PCMT_AUTO_LOG', 0);

// �����ȥڡ����Υ����ॹ����פ򹹿����������֥ڡ�����
// �����ॹ����פ򹹿�����
define('PCMT_TIMESTAMP', 0);

function plugin_pcomment_action()
{
	global $vars;

	if (! isset($vars['msg']) || $vars['msg'] == '') return array();
	$refer = isset($vars['refer']) ? $vars['refer'] : '';

	$retval = pcmt_insert();
	if ($retval['collided']) {
		$vars['page'] = $refer;
		return $retval;
	}

	pkwk_headers_sent();
	header('Location: ' . get_script_uri() . '?' . rawurlencode($refer));
	exit;
}

function plugin_pcomment_convert()
{
	global $script, $vars;
	global $_pcmt_messages;

	// �����
	$ret = '';

	// �ѥ�᡼���Ѵ�
	$params = array(
		'noname'=>FALSE,
		'nodate'=>FALSE,
		'below' =>FALSE,
		'above' =>FALSE,
		'reply' =>FALSE,
		'_args' =>array()
	);
	array_walk(func_get_args(), 'pcmt_check_arg', & $params);

	// ʸ��������
	$vars_page = isset($vars['page']) ? $vars['page'] : '';
	$page  = (isset($params['_args'][0]) && $params['_args'][0] != '') ? $params['_args'][0] :
		sprintf(PCMT_PAGE, strip_bracket($vars_page));
	$count = (isset($params['_args'][1]) && $params['_args'][1] != '') ? $params['_args'][1] : 0;
	if ($count == 0 && $count !== '0')
		$count = PCMT_NUM_COMMENTS;

	$_page = get_fullname(strip_bracket($page), $vars_page);
	if (!is_pagename($_page))
		return sprintf($_pcmt_messages['err_pagename'], htmlspecialchars($_page));

	// �����������Ȥ��ɲä������������
	$dir = PCMT_INSERT_INS;
	if ($params['below']) {
		$dir = 0;	// ξ�����ꤵ�줿�顢form�β��� (^^;
	} elseif ($params['above']) {
		$dir = 1;
	}

	// �����Ȥ����
	list($comments, $digest) = pcmt_get_comments($_page, $count, $dir, $params['reply']);

	// �ե������ɽ��
	if ($params['noname']) {
		$title = $_pcmt_messages['msg_comment'];
		$name = '';
	} else {
		$title = $_pcmt_messages['btn_name'];
		$name = '<input type="text" name="name" size="' . PCMT_COLS_NAME . '" />';
	}

	$radio   = $params['reply'] ? '<input type="radio" name="reply" value="0" tabindex="0" checked="checked" />' : '';
	$comment = '<input type="text" name="msg" size="' . PCMT_COLS_COMMENT . '" />';

	// XSS�ȼ����к� - ���������褿�ѿ��򥨥�������
	$s_page   = htmlspecialchars($page);
	$s_refer  = htmlspecialchars($vars_page);
	$s_nodate = htmlspecialchars($params['nodate']);
	$s_count  = htmlspecialchars($count);

	$form = <<<EOD
  <div>
  <input type="hidden" name="digest" value="$digest" />
  <input type="hidden" name="plugin" value="pcomment" />
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="nodate" value="$s_nodate" />
  <input type="hidden" name="dir"    value="$dir" />
  <input type="hidden" name="count"  value="$count" />
  $radio $title $name $comment
  <input type="submit" value="{$_pcmt_messages['btn_comment']}" />
  </div>
EOD;
	if (! is_page($_page)) {
		$link   = make_pagelink($_page);
		$recent = $_pcmt_messages['msg_none'];
	} else {
		$msg    = ($_pcmt_messages['msg_all'] != '') ? $_pcmt_messages['msg_all'] : $_page;
		$link   = make_pagelink($_page, $msg);
		$recent = ! empty($count) ? sprintf($_pcmt_messages['msg_recent'], $count) : '';
	}

	return $dir ?
		"<div><p>$recent $link</p>\n<form action=\"$script\" method=\"post\">$comments$form</form></div>" :
		"<div><form action=\"$script\" method=\"post\">$form$comments</form>\n<p>$recent $link</p></div>";
}

function pcmt_insert()
{
	global $script, $vars, $now;
	global $_title_updated, $_no_name, $_pcmt_messages;

	$page = isset($vars['page']) ? $vars['page'] : '';
	if (! is_pagename($page))
		return array('msg'=>'invalid page name.', 'body'=>'cannot add comment.' , 'collided'=>TRUE);

	check_editable($page, true, true);

	$ret = array('msg' => $_title_updated, 'collided' => FALSE);

	//�����ȥե����ޥåȤ�Ŭ��
	$msg = str_replace('$msg', rtrim($vars['msg']), PCMT_MSG_FORMAT);

	$name = (! isset($vars['name']) || $vars['name'] == '') ? $_no_name : $vars['name'];
	$name = ($name == '') ? '' : str_replace('$name', $name, PCMT_NAME_FORMAT);

	$date = (! isset($vars['nodate']) || $vars['nodate'] != '1') ? str_replace('$now', $now, PCMT_NOW_FORMAT) : '';
	if ($date != '' or $name != '') {
		$msg = str_replace("\x08MSG\x08", $msg,  PCMT_FORMAT);
		$msg = str_replace("\x08NAME\x08",$name, $msg);
		$msg = str_replace("\x08DATE\x08",$date, $msg);
	}

	$reply_hash = isset($vars['reply']) ? $vars['reply'] : '';
	if ($reply_hash || ! is_page($page)) {
		$msg = preg_replace('/^\-+/', '', $msg);
	}
	$msg = rtrim($msg);

	$refer = isset($vars['refer']) ? $vars['refer'] : '';
	if (! is_page($page)) {
		$postdata = '[[' . htmlspecialchars(strip_bracket($refer)) . "]]\n\n-$msg\n";
	} else {
		//�ڡ������ɤ߽Ф�
		$postdata = get_source($page);

		// �����ξ��ͤ򸡽�
		$digest = isset($vars['digest']) ? $vars['digest'] : '';
		if (md5(join('', $postdata)) != $digest) {
			$ret['msg']  = $_pcmt_messages['title_collided'];
			$ret['body'] = $_pcmt_messages['msg_collided'];
		}

		// �����
		$level = 1;
		$pos   = 0;

		// �����Ȥγ��ϰ��֤򸡺�
		while ($pos < count($postdata)) {
			if (preg_match('/^\-/', $postdata[$pos])) break;
			++$pos;
		}
		$start_pos = $pos;

		$dir = isset($vars['dir']) ? $vars['dir'] : '';

		//��ץ饤��Υ����Ȥ򸡺�
		if ($reply_hash != '') {
			while ($pos < count($postdata)) {
				$matches = array();
				if (preg_match('/^(\-{1,2})(?!\-)(.*)$/', $postdata[$pos++], $matches)
					&& md5($matches[2]) == $reply_hash)
				{
					$level = strlen($matches[1]) + 1; //���������٥�

					// �����Ȥ������򸡺�
					while ($pos < count($postdata)) {
						if (preg_match('/^(\-{1,3})(?!\-)/',$postdata[$pos],$matches)
							&& strlen($matches[1]) < $level)
							break;
						++$pos;
					}
					break;
				}
			}
		} else {
			$pos = ($dir == 0) ? $start_pos : count($postdata);
		}

		if ($dir == '0') {
			if ($pos == count($postdata)) {
				$pos = $start_pos; //��Ƭ
			}
		} else {
			if ($pos == 0) {
				$pos = count($postdata); //����
			}
		}

		//�����Ȥ�����
		array_splice($postdata, $pos, 0, str_repeat('-', $level) . "$msg\n");

		// ����������
		$count = isset($vars['count']) ? $vars['count'] : '';
		pcmt_auto_log($page, $dir, $count, $postdata);

		$postdata = join('', $postdata);
	}
	page_write($page, $postdata, PCMT_TIMESTAMP);

	if (PCMT_TIMESTAMP) {
		// �ƥڡ����Υ����ॹ����פ򹹿�����
		if ($refer != '') touch(get_filename($refer));
		put_lastmodified();
	}

	return $ret;
}

// ����������
function pcmt_auto_log($page, $dir, $count, &$postdata)
{
	if (! PCMT_AUTO_LOG) return;

	$keys = array_keys(preg_grep('/(?:^-(?!-).*$)/m', $postdata));
	if (count($keys) < (PCMT_AUTO_LOG + $count)) return;

	if ($dir) {
		// ������PCMT_AUTO_LOG��
		$old = array_splice($postdata, $keys[0], $keys[PCMT_AUTO_LOG] - $keys[0]);
	} else {
		// �������PCMT_AUTO_LOG��
		$old = array_splice($postdata, $keys[count($keys) - PCMT_AUTO_LOG]);
	}

	// �ڡ���̾�����
	$i = 0;
	do {
		++$i;
		$_page = "$page/$i";
	} while (is_page($_page));

	page_write($_page, "[[$page]]\n\n" . join('', $old));

	// Recurse :)
	pcmt_auto_log($page, $dir, $count, $postdata);
}

//���ץ�������Ϥ���
function pcmt_check_arg($val, $key, &$params)
{
	if ($val != '') {
		$l_val = strtolower($val);
		foreach (array_keys($params) as $key) {
			if (strpos($key, $l_val) === 0) {
				$params[$key] = TRUE;
				return;
			}
		}
	}

	$params['_args'][] = $val;
}

function pcmt_get_comments($page, $count, $dir, $reply)
{
	global $_msg_pcomment_restrict;

	if (! check_readable($page, false, false))
		return array(str_replace('$1', $page, $_msg_pcomment_restrict));

	$data = get_source($page);
	$data = preg_replace('/^#pcomment\(?.*/i', '', $data);	// Avoid eternal recurse

	if (! is_array($data)) return array('', 0);

	$digest = md5(join('', $data));

	//�����Ȥ���ꤵ�줿��������ڤ���
	$num  = $cnt     = 0;
	$cmts = $matches = array();
	if ($dir) $data = array_reverse($data);
	foreach ($data as $line) {
		if ($count > 0 && $dir && $cnt == $count) break;

		if (preg_match('/^(\-{1,2})(?!\-)(.+)$/', $line, $matches)) {
			if ($count > 0 && strlen($matches[1]) == 1 && ++$cnt > $count) break;
			if ($reply) {
				++$num;
				$cmts[] = "$matches[1]\x01$num\x02" . md5($matches[2]) . "\x03$matches[2]\n";
				continue;
			}
		}
		$cmts[] = $line;
	}
	$data = $cmts;
	if ($dir) $data = array_reverse($data);
	unset($cmts, $matches);

	//�����Ȥ�����Υǡ������������
	while (! empty($data) && substr($data[0], 0, 1) != '-')
		array_shift($data);

	//html�Ѵ�
	$comments = convert_html($data);
	unset($data);

	//�����Ȥ˥饸���ܥ���ΰ���Ĥ���
	if ($reply) {
		$comments = preg_replace("/<li>\x01(\d+)\x02(.*)\x03/",
			'<li class="pcmt"><input class="pcmt" type="radio" name="reply" value="$2" tabindex="$1" />',
			$comments);
	}

	return array($comments, $digest);
}
?>