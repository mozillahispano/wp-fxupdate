<!--Begin: Options file for Actualiza Firefox-->
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Actualiza Firefox</h2>
	<form method="post" action="options.php">
		<?php 
			wp_nonce_field('update-options');
			$af_firefox=get_option('af_firefox');
			$af_firefox_esr=get_option('af_firefox_esr');
            $af_url=get_option('af_url');
		?>

		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<script type="text/javascript">
					<!--
					jQuery(document).ready(function($) {
						$('.postbox').children('h3, .handlediv').click(function(){
							$(this).siblings('.inside').toggle();
						});
						$('.mensaje').click(function(){
							$(this).hide();
							});
					});
					//-->
				</script>

				<!-- Configurar las versiones actuales de Firefox -->
				<div class="postbox">
					<div title="Clic para cerrar" class="handlediv"><br /></div>
					<h3 class="hndl"><span><?php _e('Settings the actual versions of Firefox:', 'actualiza-firefox'); ?></span></h3>
					<div class="inside">
						<table class="form-table" style="margin-top: 0">
							<tbody>
								<tr valign='top'>
									<th scope='row'><?php _e('Stable version of Firefox:', 'actualiza-firefox'); ?></th>
									<td><div style="overflow:auto;max-height:50px;">
										<input type="text" id="af_firefox" name="af_firefox" onkeyup="preview();" />
										<span id="af_firefox_yu" style="display:none;"><?php echo $af_firefox; ?></span>
									</div></td>
								</tr>
								<tr valign='top'>
									<th scope='row'><?php _e('Stable version of Firefox ESR:', 'actualiza-firefox'); ?></th>
									<td><div style="overflow:auto;max-height:50px;">
										<input type="text" id="af_firefox_esr" name="af_firefox_esr" onkeyup="preview();" />
										<span id="af_firefox_esr_yu" style="display:none;"><?php echo $af_firefox_esr; ?></span>
									</div></td>
								</tr>
                                <tr valign='top'>
									<th scope='row'><?php _e('URL for update:', 'actualiza-firefox'); ?></th>
									<td><div>
										<input type="url" size="75" id="af_url" name="af_url" onkeyup="preview();" />
										<span id="af_url_yu" style="display:none;"><?php echo $af_url; ?></span>
									</div></td>
								</tr>
							</tbody>
						</table>
                                            
                    </div>
                </div>
		     </div>
		</div>
    
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="af_firefox, af_firefox_esr, af_url" />
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save changes', 'actualiza-firefox'); ?>" />
		</p>
	</form>
	<script type="text/javascript">
		
		function previzualizar(){
			var af_firefox="", af_firefox_esr="", af_url="";

			document.getElementById('af_firefox_yu').innerHTML=document.getElementById('af_firefox').value;
			document.getElementById('af_firefox_esr_yu').innerHTML=document.getElementById('af_firefox_esr').value;
            document.getElementById('af_url_yu').innerHTML=document.getElementById('af_url').value;
		}

		//set initially stored values for 'Firefox' and 'Firefox ESR'
		document.getElementById('af_firefox').value=document.getElementById('af_firefox_yu').innerHTML;
		document.getElementById('af_firefox_esr').value=document.getElementById('af_firefox_esr_yu').innerHTML;
        document.getElementById('af_url').value=document.getElementById('af_url_yu').innerHTML;
		
		//initiate preview
		previzualizar();
		//-->
	</script>
</div>
<!--End: Options file for Actualiza Firefox-->