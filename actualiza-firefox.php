<?php
/*
Plugin Name: Actualiza Firefox
Plugin URI: https://github.com/mozillahispano/wp-fxupdate
Description: Alerta a los usuarios que están utilizando una versión desactualizada de Firefox y más.
Version: 0.5.1
Author: Yunier J. Sosa Vázquez
Author URI: http://firefoxmania.uci.cu
*/

/* Copyright 2012-2013  Yunier Sosa Vázquez (email: yjsosa@estudiantes.uci.cu)
	
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

For developed of plugin has been used the libraries ChooseLocale and PropertiesParser of Pascal Chevrel, Mozilla <pascal@mozilla.com>, Mozilla
ChooseLocale v0.6 (2012-12-09)
PropertiesParser v0.1 (2012-12-05)
*/

require_once('lib/ChooseLocale.class.php');
require_once('lib/PropertiesParser.class.php');
add_action('activate_actualiza-firefox/actualiza-firefox.php', 'actualiza_firefox_install'); //Instalación
add_action('plugins_loaded', 'actualiza_firefox_textdomain'); //Para la localizacion
register_uninstall_hook(__FILE__, 'actualiza_firefox_clean_uninstall'); //Desintalación limpia
wp_register_style('af_style', plugins_url('style.css',__FILE__)); //Los estilos
wp_enqueue_style('af_style'); //Los estilos
wp_register_script('af_script', plugins_url('actualiza-firefox-script.js',__FILE__)); //El script
wp_enqueue_script('af_script'); //El script
add_action('wp_footer', 'actualiza_firefox'); //Incrustando la función en el pie de página del sitio

function actualiza_firefox_install(){ //Activando el plugin por primera vez
	$af_firefox=get_option('af_firefox'); //Obtener la opción (si existe)
	if(!$af_firefox){ //Creo las opciones del plugin
		update_option('af_firefox', '21.0');
		update_option('af_firefox_esr', '17.0');
		update_option('af_url', 'http://mozilla.org/firefox');
	}	}
		
//Adicionando la página de configuracion al menú de WP
add_action('admin_menu', 'adicionar_pagina_opcion'); 
function adicionar_pagina_opcion(){
	add_options_page('Actualiza Firefox', 'Actualiza Firefox', 'manage_options','actualiza-firefox/actualiza-firefox-options.php');
}

//Internacionalización para WP
function actualiza_firefox_textdomain(){
	load_plugin_textdomain('actualiza-firefox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
}

//Localización para el idioma del usuario
function actualiza_firefox_idiomaUsuario(){
	$af_locale=new tinyL10n\ChooseLocale(array('ar', 'es', 'en', 'el', 'ff', 'fr', 'ga', 'id', 'sq', 'pt', 'lij', 'zh-TW', 'ms', 'bn-IN', 'nl', 'bn-BD'));
    $af_locale->setDefaultLocale('en');
    $af_locale->mapLonglocales = true;
    //Bypass locale detection by $_SERVER
    $af_lang=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $af_locale->setCompatibleLocale($lang);
    $af_lang=$af_locale->getDetectedLocale();

    $af_lang_file=tinyL10n\PropertiesParser::propertiesToArray(__DIR__ . '/lang/' . $af_lang . '.properties');
	
	return $af_lang_file;
}

//Eliminando las opciones del plugin cuando se elimine desde la administración de WP
function actualiza_firefox_clean_uninstall(){
	$option=get_option('af_firefox');
	if($option){ //Si existe, las elimino
		delete_option('af_firefox');
		delete_option('af_firefox_esr');
		delete_option('af_url');
	}	}

//Adicionando un vínculo hacia la configuración del plugin bajo el menú "Opciones" de WP
$plugin=plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'af_mi_vinculo_al_plugin'); 
function af_mi_vinculo_al_plugin($link){ 
	$af_con_link='<a href="options-general.php?page=actualiza-firefox/actualiza-firefox-options.php">'. __('Settings', 'actualiza-firefox').'</a>'; 
	array_unshift($link, $af_con_link); 
	return $link; }

//Obteniendo las opciones de plugin
$af_firefox=get_option('af_firefox');
$af_firefox_esr=get_option('af_firefox_esr');
$af_url=get_option('af_url');

//Obtener la versión de Firefox del usuario
function af_detectar_version_firefox($title){
	global $useragent;
	$start=$title;
	//Grab the browser version if its present
	preg_match('/'.$start.'[\ |\/]?([.0-9a-zA-Z]+)/i', $useragent, $regmatch);
	$version=$regmatch[1];

	return $version;   }

//Detectar navegador web
function af_detectar_navegador(){
	global $useragent;
	if(preg_match('/Firefox/i', $useragent))
		$title="Firefox";
	else
		$title="Otro";
			
	return $title; }

//Función para comparar las versiones de Firefox
function af_comparar_versiones($ver){
	global $af_firefox, $af_firefox_esr, $ver;
	$obsoleta=false;  //Vamos a esperar que el usuario siempre está actualizado
	$af_estable=explode('.', $af_firefox); //Dividiendo el número de versión en arreglos para poder compararlos
	$af_esr=explode('.', $af_firefox_esr);
	$af_usuario=explode('.', $ver);
	/*Analizamos que versión usa el usuario. Si la versión estable es mayor que la del usuario y el usuario
	no está usando una versión ESR entonces obsoleta=true */
    if ((((int)$af_estable[0])>((int)$af_usuario[0])) && (((int)$af_esr[0])!==((int)$af_usuario[0])))
		$obsoleta=true;
	/* Código obsoleto posterior a Firefox 16 pues ya no se envía la cadena Firefox 16.*.* sólo 16.0 (se necesita ayuda para obtener la versión real de Firefox)
	//Comprobamos si es un Firefox Estable
	elseif((((int)$af_estable[0])==((int)$af_usuario[0]))){
	   //Analizamos el tamaño de los arreglos para ver si es *.0 o *.0.*  
	   if((count($af_usuario))==(count($af_estable))){ 
	      if (((int)$af_estable[2])>((int)$af_usuario[2]))
	         $obsoleta=true;
        }    }
	//Si arriba no se termina entonces es un Firefox ESR
	elseif ((((int)$af_esr[0])==((int)$af_usuario[0]))){
	   //Analizamos el tamaño de los arreglos para ver si es *.0 o *.0.* 
	   if((count($af_usuario))==(count($af_esr))){ 
	       if (((int)$af_esr[2])>((int)$af_usuario[2]))
	           $obsoleta=true;
	   }   } */
	
	return $obsoleta;  }
	
//Función para mostrar los mensajes de Actualización
function actualiza_firefox(){
   global $useragent, $af_firefox, $af_firefox_esr, $af_url, $ver;
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	$webbrowser=af_detectar_navegador();
	$inicio='<div id="af_actualiza_firefox">';
	$af_lang_file=actualiza_firefox_idiomaUsuario();
	$jquery="<script type=\"text/javascript\">
        jQuery(document).ready(function($){
		$('#cerrarMensaje').click(function(){
			$('#af_actualiza_firefox').slideUp();
			$('body').css({\"margin-top\":\"0 !important\"});
			apagarActualiza();
			});
	   });</script>";
	if(strpos($useragent,"Firefox")){
		$ver=af_detectar_version_firefox($webbrowser);
		if(af_comparar_versiones($ver))
			echo $jquery.$inicio.$af_lang_file['closeFirefox'].$af_lang_file['downloadFirefox1'].$af_url.$af_lang_file['downloadFirefox2'].$af_lang_file['alertFirefoxNoUpdated'];
		
	}else
		echo $jquery.$inicio.$af_lang_file['closeOther'].$af_lang_file['downloadOther1'].$af_url.$af_lang_file['downloadOther2'].$af_lang_file['alertNoFirefox'];
}
?>