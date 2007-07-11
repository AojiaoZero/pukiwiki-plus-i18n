<?php
/**
 * PukiWiki Plus! livedoor 認証処理
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: livedoor.inc.php,v 0.1 2007/07/11 20:27:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_livedoor.cls.php');

function plugin_livedoor_init()
{
	$msg = array(
	  '_livedoor_msg' => array(
		'msg_logout'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by livedoor."),
		'msg_invalid'		=> _("The function of livedoor is invalid."),
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),
		'msg_not_start'		=> _("The session is not start."),
		'msg_livedoor'		=> _("livedoor"),
		'btn_login'		=> _("LOGIN(livedoor)"),
          )
        );
        set_plugin_messages($msg);
}

function plugin_livedoor_convert()
{
        global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return '<p>'.$_livedoor_msg['msg_invalid'].'</p>';

	if (! function_exists('pkwk_session_start')) return '<p>'.$_livedoor_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_livedoor_msg['msg_not_start'].'</p>';

	$name = auth_livedoor::livedoor_session_get();
	if (isset($name['name'])) {
		$logout_url = $script.'?plugin=livedoor';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}

		return <<<EOD
<div>
	<label>livedoor</label>:
	{$name['name']}
	(<a href="$logout_url">{$_livedoor_msg['msg_logout']}</a>)
</div>

EOD;
	}

	// ボタンを表示するだけ
	$login_url = $script.'?plugin=livedoor';
	if (! empty($vars['page'])) {
		$login_url .= '&amp;page='.rawurlencode($vars['page']);
	}
	$login_url .= '&amp;login';

	return <<<EOD
<form action="$login_url" method="post">
	<div>
		<input type="submit" value="{$_livedoor_msg['btn_login']}" />
	</div>
</form>

EOD;

}

function plugin_livedoor_inline()
{
	global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return $_livedoor_msg['msg_invalid'];

	if (! function_exists('pkwk_session_start')) return $_livedoor_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_livedoor_msg['msg_not_start'];

	$name = auth_livedoor::livedoor_session_get();
	if (isset($name['name'])) {
		$logout_url = $script.'?plugin=livedoor';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}
		return sprintf($_livedoor_msg['msg_logined'],$name['name']) .
			'(<a href="'.$logout_url.'">'.$_livedoor_msg['msg_logout'].'</a>)';
	}

	$login_url = plugin_livedoor_jump_url(1);
	return '<a href="'.$login_url.'">'.$_livedoor_msg['msg_livedoor'].'</a>';
}

function plugin_livedoor_action()
{
	global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return '';
	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	$r_page = (empty($vars['page'])) ? '' : rawurlencode( decode($vars['page']) );

	// LOGIN
	if (isset($vars['login'])) {
		header('Location: '. plugin_livedoor_jump_url());
		die();
        }
	// LOGOUT
	if (isset($vars['logout'])) {
		auth_livedoor::livedoor_session_unset();
		header('Location: '.$script.'?'.$r_page);
		die();
	}

	// AUTH
	$obj_livedoor = new auth_livedoor($auth_api['livedoor']['sec_key'],$auth_api['livedoor']['app_key']);
	$rc = $obj_livedoor->auth($vars);

	if (! isset($rc['has_error']) || $rc['has_error'] == 'true') {
		// ERROR
		$body = (isset($rc['message'])) ? $rc['message'] : 'unknown error.';
		die_message($body);
	}

	$obj_livedoor->livedoor_session_put();
	$r_page = rawurlencode($obj_livedoor->get_return_page());
	header('Location: '.$script.'?'.$r_page);
	die();
}

function plugin_livedoor_jump_url($inline=0)
{
	global $auth_api,$vars;
	$obj = new auth_livedoor($auth_api['livedoor']['sec_key'],$auth_api['livedoor']['app_key']);
	$url = $obj->make_login_link($vars['page']);
	return ($inline) ? $url : str_replace('&amp;','&',$url);
}

function plugin_livedoor_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['livedoor']['use']) return array(ROLE_GUEST,'','','');
	$msg = auth_livedoor::livedoor_session_get();
	if (! empty($msg['name'])) return array(ROLE_AUTH_LIVEDOOR,$msg['name'],$msg['name'],'');
	return array(ROLE_GUEST,'','','');
}

?>
