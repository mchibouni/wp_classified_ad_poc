				<?php  
					$current_user = wp_get_current_user();
					
					if ( ! yiw_get_option( 'show_linksbar' ) )
						return;
				?>
				<ul id="linksbar" class="group">
		            
		            <?php if ( function_exists( 'yiw_minicart' ) && yiw_get_option( 'show_linksbar_cart' ) ) : ?>
		        	<li class="icon cart">
						<?php yiw_minicart(); ?>
					</li>         
		        	<?php endif; ?>
		            
		            <?php if ( yiw_get_option( 'show_linksbar_signin' ) && ! $current_user->ID ) : ?>
		        	<li class="icon pencil">
						<a href="<?php echo add_query_arg(array( 'action' => 'register' ), site_url('wp-login.php', 'login')) ?>"><?php echo apply_filters( 'yiw_linksbar_signin_text', __('Sign in', 'yiw') ) ?></a> | 
					</li>         
		        	<?php endif; ?>
		            
		            <?php if ( yiw_get_option( 'show_linksbar_login' ) ) : ?>
		        	<li class="icon lock">
		        		<?php if ( $current_user->ID != 0 ) : ?>                       
						<a href="<?php echo wp_logout_url( yiw_curPageURL() ); ?>"><?php _e('Logout', 'yiw') ?></a> |
						<?php else : ?>      
						<a href="<?php echo wp_login_url( yiw_curPageURL() ); ?>"><?php _e('Login', 'yiw') ?></a> |  
						<?php endif; ?>
					</li>         
		        	<?php endif; ?>   
		        	
		        	<?php 
						$args = array(
							'container' => 'none', 
							'fallback_cb' => 'wp_page_menu', 
							'items_wrap' => '%3$s',
							'after' => ' | ',
	        				'depth' => 1, 
							'theme_location' => 'linksbar',
							'fallback_cb' => ''
						);
						
						wp_nav_menu( $args );
					?>
		        
		        </ul>