<?php
/**
 *�����������ե�����
 */

$switchHash["#"] = SPECIAL_IDENTIFIRE;  // # ����Ϥޤ�ͽ��줢��

// ���������
$switchHash["/"] = COMMENT;        //  �����Ȥ� /* ���� */ �ޤǤ� // ������Ԥޤ�
$code_comment = Array(
	"/" => Array(
		"/^\/\/.*\\n/",
		"/^\/\*(.|\n)*?\*\//",
	)
);

// �����ȥ饤����
if($mkoutline){
  $switchHash["{"] = BLOCK_START;
  $switchHash["}"] = BLOCK_END;
}

$code_css = Array(
  'operator',		// ���ڥ졼���ؿ�
  'identifier',	// ����¾�μ��̻�
  'pragma',		// module, import �� pragma
  'system',		// �������Ȥ߹��ߤ��� __stdcall �Ȥ�
  );

$code_keyword = Array(
  	'operator' => 1,

		'asm' => 2,
		'auto' => 2,
		'extern' => 2,
		'inline' => 2,
		'private' => 2,
		'protected' => 2,
		'public' => 2,
		'register' => 2,
		'virtual' => 2,
  	
		'if' => 2,
		'for' => 2,
		'goto' => 2,
		'switch' => 2,
		'while' => 2,
		'do' => 2,
		'endif' => 2,
		'else' => 2,
		'case' => 2,
		'default' => 2,
		'break' => 2,
		'continue' => 2,
		'return' => 2,
  	
		'const' => 2,
		'static' => 2,
		'friend' => 2,
		'false' => 2,
		'true' => 2,
  	
		'signed' => 2,
		'unsigned' => 2,
		'void' => 2,
		'bool' => 2,
		'char' => 2,
		'short' => 2,
		'int' => 2,
		'long' => 2,
		'float' => 2,
		'double' => 2,
		'this' => 2,
  	
		'sizeof' => 2,

  	'enum' => 2,
		'struct' => 2,
		'union' => 2,
		'class' => 2,
  	
		'delete' => 2,
		'new' => 2,
  	
		'try' => 2,
		'catch' => 2,
		'throw' => 2,
		'explicit' => 2,
		'mutable' => 2,
		'template' => 2,
		'volatile' => 2,

		'#define' => 3,
		'#elif' => 3,
		'#else' => 3,
		'#endif' => 3,
		'#error' => 3,
		'#if' => 3,
		'#ifdef' => 3,
		'#ifndef' => 3,
		'#include' => 3,
		'#line' => 3,
		'#pragma' => 3,
		'#undef' => 3,
		'typedef' => 3,
		'typename' => 3,
		'namespace' => 3,
		'using' => 3,

		'__declspec' => 4,
		'__FILE__' => 4,
  );
?>