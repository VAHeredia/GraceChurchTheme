					<div class="logo">
						<a href="<?php echo esc_url( home_url('/') ); ?>"><?php
							echo !empty($GRACE_CHURCH_GLOBALS['logo'])
								? '<img src="'.esc_url($GRACE_CHURCH_GLOBALS['logo']).'" class="logo_main" alt="">'
								: ''; 
							echo ($GRACE_CHURCH_GLOBALS['logo_text']
								? '<div class="logo_text">'.($GRACE_CHURCH_GLOBALS['logo_text']).'</div>'
								: '');
							echo ($GRACE_CHURCH_GLOBALS['logo_slogan']
								? '<br><div class="logo_slogan">' . esc_html($GRACE_CHURCH_GLOBALS['logo_slogan']) . '</div>'
								: '');
						?></a>
					</div>
