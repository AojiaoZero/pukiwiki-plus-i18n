<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: aname.inc.php,v 1.17.1 2005/01/23 07:00:16 miko Exp $
//
// aname plugin - Set an anchor <a name="key"> to link

function plugin_aname_inline()
{
	$args = func_get_args();
	return call_user_func_array('plugin_aname_convert', $args);
}

function plugin_aname_convert()
{
	global $script, $vars;
	global $html_transitional;

	if (func_num_args() < 1) return FALSE;

	$args = func_get_args();
	$id = array_shift($args);
	if (! preg_match('/^[A-Za-z][\w\-]*$/', $id)) return FALSE;

	$body = ! empty($args) ? preg_replace('/<\/?a[^>]*>/', '', array_pop($args)) : '';

	$class   = in_array('super', $args) ? 'anchor_super' : 'anchor';
	$url     = in_array('full',  $args) ? $script . '?' . rawurlencode($vars['page']) : '';
	$attr_id = in_array('noid',  $args) ? '' : ' id="' . $id . '"';

	// ���Ӥ�xhtml�б����Ƥʤ���Τ�¿�����ḽ�²�
	if ($html_transitional) {
		$attr_id = in_array('noid', $args) ? '' : ' id="' . $id . '" name="' . $id . '"';
	} elseif (!defined('UA_PROFILE') || UA_PROFILE == 'default') {
		$attr_id = in_array('noid', $args) ? '' : ' id="' . $id . '"';
	} else {
		$attr_id = in_array('noid', $args) ? '' : ' id="' . $id . '" name="' . $id . '"';
	}

//	return "<a class=\"$class\"$attr_id href=\"$url#$id\" title=\"$id\">$body</a>";
//miko	XHTML1.1���б����뤿��ˤ� $attrid ���ʤ��ۤ�������
	return "<a class=\"$class\" href=\"$url#$id\" title=\"$id\">$body</a>";
}
?>
