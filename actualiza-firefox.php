<?php
/*
Plugin Name: Actualiza Firefox
Plugin URI: https://github.com/mozillahispano/wp-fxupdate
Description: Un simple plugin para aletar a los usarios que est&aacute;n utilizando una versi&oacute;n obsoleta de Firefox y m&aacute;s.
Version: 0.1
Author: Yunier Sosa V&aacute;zquez
Author URI: http://firefoxmania.uci.cu
Contributor: Erick Le&oacute;n Bolinaga, Roberto Nuñez
*/

/* Copyright 2012-2012  Yunier Sosa V&aacute;zquez  (email: yuniers@gmail.com)
	
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
*/

if($_COOKIE['actualiza_firefox']=='on'){
    //no hago nada porque la cookie ya existe y tiene valor 0 
}
elseif($_COOKIE['actualiza_firefox']=='off'){
    //no hago nada porque la cookie ya existe y tiene valor 1 y no debo seguir incrementando el tiempo de vida
}
elseif($_COOKIE['actualiza_firefox']==0){
    $_COOKIE['actualiza_firefox'];
    setcookie('actualiza_firefox', 'on', time()+60*60*24*10);
}

// Pre-2.6 compatibility
if(!defined('WP_CONTENT_URL'))
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if(!defined('WP_CONTENT_DIR'))
	define('WP_CONTENT_DIR', ABSPATH.'wp-content');
if(!defined('WP_PLUGIN_URL'))
	define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
if(!defined('WP_PLUGIN_DIR'))
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

//Plugin Options
$af_firefox=get_option('af_firefox');
$af_firefox_esr=get_option('af_firefox_esr');
$af_url=get_option('af_url');

//Detect webbrowser versions
function detectar_version_firefox($title){
	global $useragent;

	$start=$title;

	//Grab the browser version if its present
	preg_match('/'.$start.'[\ |\/]?([.0-9a-zA-Z]+)/i', $useragent, $regmatch);
	$version=$regmatch[1];

	return $version;
}

//Detect webbrowsers
function detectar_navegador(){
	global $useragent;
	if(preg_match('/Firefox/i', $useragent))
		$title="Firefox";
	else
		$title="Otro";
			
	$web_browser=$title;
	
	return $web_browser;
}

//Función para comparar las versiones de Firefox
function comparar_versiones($ver){
	global $af_firefox, $af_firefox_esr, $ver;
	$obsoleta=false;  //Vamos a esperar qeu el usuario esté siempre actualizado
	//Dividiendo el número de versión en arreglos para poder compararlos
	$af_estable=explode('.', $af_firefox);
	$af_esr=explode('.', $af_firefox_esr);
	$af_usuario=explode('.', $ver);
	/*Analizamos que versión usa el usuario. Si la versión estable es mayor que la del usuario y el usuario
	no está usando una versión ESR entonces obsoleta=true */
    if ((((int)$af_estable[0])>((int)$af_usuario[0])) && (((int)$af_esr[0])!==((int)$af_usuario[0]))){
		$obsoleta=true;
	}
    //Comparamos si es un Firefox estable
	elseif((((int)$af_estable[0])==((int)$af_usuario[0]))){
		if((count($af_usuario))==(count($af_estable))){ /*Analizamos el tamaño de los arreglos para ver si es *.0 u *.0.* porque al comparar en [2] explota, si se cumple analizamos el arreglo en [2] sino */
			if ((strcmp($af_estable[1], $af_usuario[1]))<0)
                $obsoleta=true;
            elseif (((int)$af_estable[2])>((int)$af_usuario[2]))
				$obsoleta=true;
		}
        else
			$obsoleta=true;
	}
	//Si arriba no se termina entonces es un Firefox ESR
	elseif ((((int)$af_esr[0])==((int)$af_usuario[0]))){
	   if((count($af_usuario))>(count($af_esr)))//Comprobamos si
            $obsoleta=true; 
	   elseif((count($af_usuario))==(count($af_esr))){ /*Analizamos el tamaño de los arreglos para ver si es *.0 u *.0.* porque al comparar en [2] explota, si se cumple analizamos el arreglo en [2] sino */
	       if (((int)$af_esr[2])>((int)$af_usuario[2]))
	           $obsoleta=true;
	   }
	   else
          $obsoleta=true;
	}
	
	return $obsoleta;
}

//Function principal para mostrar los mensajes
function actualiza_firefox(){
    global $useragent, $af_firefox, $af_firefox_esr, $af_url, $ver;
    $useragent=$_SERVER['HTTP_USER_AGENT'];
	 $webbrowser=detectar_navegador();
	 $estilos='<div class="mensaje"><div class="inner_padding">';
    $jquery="<script type=\"text/javascript\">
    jQuery(document).ready(function($){
	$('#btnCerrar').click(function(){
			$('.mensaje').slideUp();
			$('body').css({\"margin-top\":\"0 !important\"});
			writeCookie('actualiza_firefox', 'off', '240');
        });
	});
	function writeCookie(name, value, hours){
	    var expire ='';
	    if(hours != null){
	        expire = new Date((new Date()).getTime() + hours * 3600000);
	        expire = \"; expires=\" + expire.toGMTString();
	    }
	    document.cookie = name + \"=\" + escape(value) + expire;
	}
    </script>";
	$descarga="<a class=\"vinculo\" target='blank_' href='$af_url'>En este sitio</a>";
	$cerrar="<a class=\"cerrar\" title='Cerrar' id=\"btnCerrar\" href='#'>X</a>";
    $ver=detectar_version_firefox($webbrowser);
    $current_user = wp_get_current_user();
    $text='';
    if ($_COOKIE['actualiza_firefox']=='on'){
        if (strpos($useragent,"Firefox")){
            if (comparar_versiones($ver)){
                if(0 == $current_user->ID)
                    $text=$estilos.'Hey! Tu Firefox est&aacute; desactualizado. '.$descarga.' podr&aacute;s encontrar la &uacute;ltima versi&oacute;n estable.'.$cerrar.'</div></div>';
                else
                    $text=$estilos.'Hey '.$current_user->display_name.'! Tu Firefox est&aacute; desactualizado. '.$descarga.' podr&aacute;s encontrar la &uacute;ltima versi&oacute;n estable.'.$cerrar.'</div></div>';
                }
        }
	    else{
            if(0 == $current_user->ID)
                $text=$estilos.'Hey! &iquest;Te gustan los temas relacionados con Firefox? &iquest;Haz pensado probar este navegador? '.$descarga. ' podr&aacute;s encontrar la &uacute;ltima versi&oacute;n estable.'.$cerrar.'</div></div>';
            
            else
                $text=$estilos.'Hey '.$current_user->display_name.'! &iquest;Te gustan los temas relacionados con Firefox? &iquest;Haz pensado probar este navegador? '.$descarga. ' podr&aacute;s encontrar la &uacute;ltima versi&oacute;n estable.'.$cerrar.'</div></div>';
            
        }
    }
    
    echo $text.$jquery.$btnCerrar;
}

//El estilo que usaremos para el plugin 
wp_register_style('af_style', WP_PLUGIN_URL . '/actualiza-firefox/style.css');
wp_enqueue_style('af_style');

//Adicionando la página de configuracion al menu de WP
add_action('admin_menu', 'adicionar_pagina_opcion');

function adicionar_pagina_opcion(){
	add_options_page('Actualiza Firefox', 'Actualiza Firefox', 'manage_options','actualiza-firefox/actualiza-firefox-options.php');
}
//Incrustando la función en la cabecera del sitio, un lugar que atrea mucho la atención
add_action('wp_head', 'actualiza_firefox');

//Adicionando un vinculo hacia la configuracion del plugin
$plugin=plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'mi_vinculo_al_plugin' ); 
function mi_vinculo_al_plugin( $link ) { 
	// Adicionando un vinculo hacia la configuracion del plugin
	$con_link='<a href="options-general.php?page=actualiza-firefox/actualiza-firefox-options.php">Configurar</a>'; 
	array_unshift( $link, $con_link ); 
	return $link; 
}
?>
