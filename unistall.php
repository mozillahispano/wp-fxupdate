<?php
    if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();
    /* Borro las opciones de Actualiza Firefox de la BD de WordPress */
    delete_option('af_firefox');
    delete_option('af_firefox_esr');
    delete_option('af_url');  ?>