<?php
/* 
Plugin Name: questquest integration
Plugin URI: http://sayta.net 
Description: Интеграция с сервером questquest.ru
Author: Maksim Rep 
Version: 1.0 
Author URI: http://sayta.net
*/


define('QQ_VER', 'QQ-1.0');
define('QQ_DIR', plugin_dir_path( __FILE__ ));
define('QQ_URI', plugin_dir_url( __FILE__ ));
 
	
/*================================================Добавим пункт меню в админпанель============================================*/

function qq_add_menu_page() {
	add_menu_page(  
		__('Интеграция для QQ', 'qq'), //Текст, который будет использован в теге <title> на странице, относящейся к пункту меню.
		__('QQ', 'qq'), //Название пункта меню в сайдбаре админ-панели. 
		'manage_options', //Права пользователя (возможности), необходимые чтобы пункт меню появился в списк
		'qq-ru', //Уникальное название (slug), по которому затем можно обращаться к этому меню.
		'qq_display_admin_page_default',//Название функции, которая выводит контент страницы пункта меню.Этот параметр необязательный и если он не указан, WordPress ожидает что текущий подключаемый PHP файл генерирует страницу код страницы админ-меню, без вызова функции. Большинство авторов плагинов предпочитают указывать этот параметр.
		'dashicons-upload',//Иконка для пункта меню.
		"39.1");//Число определяющее позицию меню. Чем больше цифра, тем ниже будет расположен пункт меню.
		
}

function qq_display_admin_page_default() {
	include_once QQ_DIR . 'inc/qq_sidebar_admin.php';
}

add_action('admin_menu', 'qq_add_menu_page');
