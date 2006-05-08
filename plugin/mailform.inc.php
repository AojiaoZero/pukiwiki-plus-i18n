<?php
// $Id: mailform.inc.php,v 1.1.1 2006/05/08 16:12:29 miko Exp $
// Copyright (C)
//   2006      PukiWiki Plus! Team
//   2002      Originally written by OKAWARA, Satoshi <kawara@dml.co.jp>
//             http://www.dml.co.jp/~kawara/pukiwiki/pukiwiki.php
//
// article: MAIL form plugin
// require: mbstring

/*
 LANGUAGE�ե�����˲������ͤ��ɲä��Ƥ��餴���Ѥ�������
	$_title_mailsend	= '$1 �����������ޤ���';
	$_title_mailconfirm	= '$1 ���������';

	$_btn_mailsend		= '�嵭�����Ƥ�����';
	$_btn_mailconfirm	= '��ǧ';
	$_btn_fromaddress	= '&nbsp;&nbsp;&nbsp; From: ';
	$_btn_mailsubject	= 'Subject: ';
	$_title_youraddress = '(���ʤ��Υ᡼�륢�ɥ쥹)';
*/
define('PLUGIN_MAILFORM_COLS', 70);      // �ƥ����ȥ��ꥢ�Υ�����
define('PLUGIN_MAILFORM_ROWS', 10);      // �ƥ����ȥ��ꥢ�ιԿ�
define('PLUGIN_MAILFORM_NAME_COLS', 24); // ̾���ƥ����ȥ��ꥢ�Υ�����
define('PLUGIN_MAILFORM_FROMADDRESS_COLS', 60); // �᡼�륢�ɥ쥹�ƥ����ȥ��ꥢ�Υ�����
define('PLUGIN_MAILFORM_SUBJECT_COLS', 60);     // ��̾�ƥ����ȥ��ꥢ�Υ�����
define('PLUGIN_MAILFORM_AUTO_BR', 1);           // ���Ԥ�ưŪ�Ѵ� 1:���� 0:���ʤ�
define('PLUGIN_MAILFORM_SUBJECT_PREFIX', '[PukiWikiMail]'); // ������ƤΥ᡼������������̾
define('PLUGIN_MAILFORM_NOSUBJECT', 'No Subject');          // ��̾��̤�����ξ���ɽ��

// ������ƤΥ᡼���ۿ���(ʣ���ϥ���ޤǶ��ڤ�)
// �㡧 'mail1@example.com'
// �㡧 'mail1@example.com, mail2@example.com'
define('PLUGIN_MAILFORM_MAILTO', '');

function plugin_mailform_action()
{
	global $script, $post, $vars;
	global $_title_mailsend, $_title_mailconfirm, $_btn_mailsend;
	global $smtp_server, $smtp_auth, $_after_pop;

	if (PLUGIN_MAILFORM_MAILTO == '') { die('Plugin mailform require email address. Please setup.'); }

	if (!isset($_title_mailsend))    { $_title_mailsend    = '$1 �����������ޤ���'; }
	if (!isset($_title_mailconfirm)) { $_title_mailconfirm = '$1 ���������'; }
	if (!isset($_btn_mailsend))      { $_btn_mailsend      = '�嵭�����Ƥ�����'; }

	if (!isset($post['msg']) || $post['msg'] == '')
		return;

	$subject = (isset($post['subject']) && $post['subject'] != '') ? $post['subject'] : PLUGIN_MAILFORM_NOSUBJECT;

	// If unknown command, failed result.
	if ($post['pcmd'] != 'confirm' && $post["pcmd"] != 'send') return;

	// command option
	if ($post["pcmd"] == 'confirm') {
		$title = $_title_mailconfirm;

		$button = '<input type="submit" name="mailform" value="' . $_btn_mailsend . '">' . "\n";

		$body = '<form action="' . $script . '" method="post">' . "\n"
			  . '<input type="hidden" name="refer" value="' . $vars['refer'] . '">' . "\n"
			  . '<input type="hidden" name="digest" value="' . $digest . '">' . "\n"
			  . '<input type="hidden" name="plugin" value="mailform">' . "\n"
			  . '<input type="hidden" name="pcmd" value="send">' . "\n"
			  . '<input type="hidden" name="fromaddress" value="' . $vars['fromaddress'] . '">' . "\n"
			  . '<input type="hidden" name="subject" value="' . $vars['subject'] . '">' . "\n"
			  . '<input type="hidden" name="msg" value="' . $vars['msg'] . '">' . "\n"
			  . '<pre>' . "\n"
			  . '    From: ' . $vars['fromaddress'] . "\n"
			  . ' Subject: ' . $vars['subject'] . "\n"
			  . "\n\n"
			  . $post['msg']
			  . '</pre>' . "\n"
			  . '<div align="center">' . $button . '</div>'
			  . '</form>';

		$retvars['msg'] = $title;
		$retvars['body'] = $body;
		return $retvars;
	} else if($post['pcmd'] == 'send') {
		$title = $_title_mailsend;

		$mailaddress = PLUGIN_MAILFORM_MAILTO;
		$mailsubject = PLUGIN_MAILFORM_SUBJECT_PREFIX . ' ' . $subject;
		$mailsubject = mb_encode_mimeheader($mailsubject);

		$mailbody = '';
		$mailbody .= '  Page: ' . $post['refer'] . "\n";
		$mailbody .= '�� URL: ' . $script . '?' . rawurlencode($post['refer']) . "\n";
		$mailbody .= "---\n\n";
		$mailbody .= $post['msg'];

		$mailaddheader = '"From: ' . $post['fromaddress'];

		// Wait POP/APOP auth completion
		if ($_after_pop) {
			$result = pop_before_smtp();
			if ($result !== TRUE) die($result);
		}

		ini_set('SMTP', $smtp_server);
		mb_language(LANG);
		mb_send_mail($mailaddress, $mailsubject, $mailbody, $mailaddheader);
	}

	// Clear "is_page" cache.
	is_page($post['refer'], TRUE);

	$retvars['msg'] = $title;
	$retvars['body'] = $body;

	$post['page'] = $post['refer'];
	$vars['page'] = $post['refer'];

	return $retvars;
}

function plugin_mailform_convert()
{
	global $script, $vars, $digest;
	global $_btn_mailsend, $_btn_mailconfirm, $_btn_fromaddress, $_btn_mailsubject, $_title_youraddress;

	if (PLUGIN_MAILFORM_MAILTO == '') { die('Plugin mailform require email address. Please setup.'); }

	if (!isset($_btn_mailsend))      { $_btn_mailsend = '�嵭�����Ƥ�����'; }
	if (!isset($_btn_mailconfirm))   { $_btn_mailconfirm = '��ǧ'; }
	if (!isset($_btn_fromaddress))   { $_btn_fromaddress = '&nbsp;&nbsp;&nbsp; From: '; }
	if (!isset($_btn_mailsubject))   { $_btn_mailsubject = 'Subject: '; }
	if (!isset($_title_youraddress)) { $_title_youraddress = '(���ʤ��Υ᡼�륢�ɥ쥹)'; }

	if((arg_check('read')||$vars['cmd'] == ''||arg_check('unfreeze')||arg_check('freeze')||$vars['write']||$vars['article'])) {
		$button = '<input type="submit" name="mailform" value="' . $_btn_mailconfirm . '">' . "\n";
	}

	$html = '<form action="' . $script . '" method="post">' . "\n"
		  . '<input type="hidden" name="refer" value="' . $vars['page'] . '">' . "\n"
		  . '<input type="hidden" name="digest" value="' . $digest . '">' . "\n"
		  . '<input type="hidden" name="plugin" value="mailform">' . "\n"
		  . '<input type="hidden" name="pcmd" value="confirm">' . "\n"
		  . $_btn_fromaddress . '<input type="text" name="fromaddress" size="' . PLUGIN_MAILFORM_FROMADDRESS_COLS . '"> '
		  . '<span class="small">' . $_title_youraddress . "</span><br>\n"
		  . $_btn_mailsubject . '<input type="text" name="subject" size="' . PLUGIN_MAILFORM_SUBJECT_COLS . '"> <br>' . "\n"
		  . '<textarea name="msg" rows="' . PLUGIN_MAILFORM_ROWS . '" cols="' . PLUGIN_MAILFORM_COLS . '">' . "\n</textarea><br />\n"
		  . $button
		  . '</form>';

	return $html;
}
?>