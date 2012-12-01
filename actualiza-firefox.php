<?php
/*
Plugin Name: Actualiza Firefox
Plugin URI: https://github.com/mozillahispano/wp-fxupdate
Description: Alerta a los usarios que est&aacute;n utilizando una versi&oacute;n obsoleta de Firefox y m&aacute;s.
Version: 0.3
Author: Yunier Sosa V&aacute;zquez
Author URI: http://firefoxmania.uci.cu
Contributor: Erick León Bolinaga, Roberto Nuñez
*/

/* Copyright 2012-2012  Yunier Sosa Vázquez  (email: yjsosa@estudiantes.uci.cu)
	
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

if(!isset($_COOKIE['actualiza_firefox'])){
    $_COOKIE['actualiza_firefox'];
    setcookie('actualiza_firefox', 'on', time()+60*60*24*30, '/'); //Creando la cookie con 30 dias de duracion
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

	return $version;   }

//Detect webbrowsers
function detectar_navegador(){
	global $useragent;
	if(preg_match('/Firefox/i', $useragent))
		$title="Firefox";
	else
		$title="Otro";
			
	return $title; }

//Función para comparar las versiones de Firefox
function comparar_versiones($ver){
	global $af_firefox, $af_firefox_esr, $ver;
	$obsoleta=false;  //Vamos a esperar qeu el usuario esté siempre actualizado
	$af_estable=explode('.', $af_firefox); //Dividiendo el número de versión en arreglos para poder compararlos
	$af_esr=explode('.', $af_firefox_esr);
	$af_usuario=explode('.', $ver);
	/*Analizamos que versión usa el usuario. Si la versión estable es mayor que la del usuario y el usuario
	no está usando una versión ESR entonces obsoleta=true */
    if ((((int)$af_estable[0])>((int)$af_usuario[0])) && (((int)$af_esr[0])!==((int)$af_usuario[0])))
		$obsoleta=true;
    //Comprobamos si es un Firefox Estable
	elseif((((int)$af_estable[0])==((int)$af_usuario[0]))){
	   /*Analizamos el tamaño de los arreglos para ver si es *.0 o *.0.*  */
	   if((count($af_usuario))==(count($af_estable))){ 
	      if (((int)$af_estable[2])>((int)$af_usuario[2]))
	         $obsoleta=true;
        }    }
	//Si arriba no se termina entonces es un Firefox ESR
	elseif ((((int)$af_esr[0])==((int)$af_usuario[0]))){
	   /*Analizamos el tamaño de los arreglos para ver si es *.0 o *.0.* */
	   if((count($af_usuario))==(count($af_esr))){ 
	       if (((int)$af_esr[2])>((int)$af_usuario[2]))
	           $obsoleta=true;
	   }   }
	
	return $obsoleta;  }

//Funcion principal para mostrar los mensajes de alerta para actualizar
function actualiza_firefox(){
    global $useragent, $af_firefox, $af_firefox_esr, $af_url, $ver;
    if(isset($_COOKIE['actualiza_firefox'])){
        if ($_COOKIE['actualiza_firefox']=='on'){
			$useragent=$_SERVER['HTTP_USER_AGENT'];
            $webbrowser=detectar_navegador();
        	$inicio='<div class="af_actualiza_firefox">';
            $alertaFirefoxDesactualizado='<div class="af_creature">
                <span class="af_mensaje_alerta">Hey! Tu Firefox no est&aacute; actualizado.</span>
                    <div class="af_descripcion">Esto puede ocasionar que tu navegador no funcione 
                    correctamente o presente fallas de seguridad. Te recomendamos que lo actualices lo
                    m&aacute;s r&aacute;pido posible.</div></div>';
            $alertaNoFirefox='<div class="af_creature">
                <span class="af_mensaje_alerta">Hey! Prueba el &uacute;nico navegador que te pone de primero.</span>
                    <div class="af_descripcion">Firefox est&aacute; dise&ntilde;ado por Mozilla, una 
                    comunidad global que antepone sus principios a las ganancias.</div></div>';
            $descargarFirefox="<div class=\"af_actualizar\">
                <a class=\"button_actualiza_firefox\" target=\"_blank\" href='$af_url'>Actual&iacute;zalo ahora &raquo;</a></br>
                o <a class=\"af_vinculo\" target=\"_blank\" href='http://firefoxmania.uci.cu/10-razones-para-actualizarse-a-las-versiones-mas-recientes-de-firefox/'>aprende m&aacute;s</a> acerca de ello.</div>";
            $descargarOtro="<div class=\"af_actualizar\">
                <a class=\"button_actualiza_firefox\" target=\"_blank\" href='$af_url'>Descarga Firefox &raquo;</a></br>
                o <a class=\"af_vinculo\" target=\"_blank\" href='http://firefoxmania.uci.cu/10-razones-para-actualizarse-a-las-versiones-mas-recientes-de-firefox/'>aprende m&aacute;s</a> acerca de &eacute;l.</div>";
            $jquery="<script type=\"text/javascript\">
            jQuery(document).ready(function($){
                $('#btnCerrar').click(function(){
                    $('.af_actualiza_firefox').slideUp();
                    $('body').css({\"margin-top\":\"0 !important\"});
                    writeCookie('actualiza_firefox', 'off', '720', '/');
                });
			});
        	function writeCookie(name, value, hours, path){
        	    var expire ='';
				if(hours != null){
        	        expire = new Date((new Date()).getTime() + hours * 3600000);
        	        expire = \"; expires=\" + expire.toGMTString();
        	    }
				if (path){
					path=\"; path=\"+path;
				}else path=\"\";
				
        	    document.cookie = name + \"=\" + escape(value) + expire + path;
        	}
            </script>";
			$cerrar="<a class=\"af_cerrar\" title='Ocultar este mensaje' id=\"btnCerrar\">X</a>";
        	$ver=detectar_version_firefox($webbrowser);
            $text='';
            if (strpos($useragent,"Firefox")){
                if (comparar_versiones($ver))
                    $text=$inicio.$cerrar.$descargarFirefox.$alertaFirefoxDesactualizado.'</div>';
            }else
                $text=$inicio.$cerrar.$descargarOtro.$alertaNoFirefox.'</div>';
            echo $jquery.$text;
        }   }  }

//El estilo que usaremos para el plugin 
wp_register_style('af_style', WP_PLUGIN_URL . '/actualiza-firefox/style.css');
wp_enqueue_style('af_style');

add_action('admin_menu', 'adicionar_pagina_opcion'); //Adicionando la página de configuracion al menu de WP

function adicionar_pagina_opcion(){
	add_options_page('Actualiza Firefox', 'Actualiza Firefox', 'manage_options','actualiza-firefox/actualiza-firefox-options.php');
}

add_action('wp_footer', 'actualiza_firefox'); //Incrustando la función en el pie de pagina del sitio

//Adicionando un vinculo hacia la configuracion del plugin
$plugin=plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'mi_vinculo_al_plugin' ); 
function mi_vinculo_al_plugin( $link ) { 
	// Adicionando un vinculo hacia la configuracion del plugin
	$con_link='<a href="options-general.php?page=actualiza-firefox/actualiza-firefox-options.php">Configurar</a>'; 
	array_unshift( $link, $con_link ); 
	return $link; 
}  ?>