<section id="asre_content1" class="asre_tab_section <?php echo 'list' !== $tab ? 'asre_report_form_section' : ''; ?>">
	<div class="asre_tab_inner_container"> 
		<?php 
		if ( 'list' === $tab ) :
			$data = asre_pro()->admin->get_data();
			?>
		<div id="the_list" class="widefat">
			<?php if ( empty( $data ) ) : ?>
			<?php else : ?>	
			<?php 
				foreach ( $data as $w_data ) : 
					if ( isset( $w_data->report_status ) && 'draft' == $w_data->report_status ) { 
						continue;
					} 
					?>
				<div id="report-row" class="<?php echo esc_html($w_data->email_enable) ? 'active' : 'inactive'; ?>" value="<?php echo esc_html($w_data->id); ?>">
					<?php 
					if ( $w_data->email_enable ) {
						$checked = 'checked';
					} else {
						$checked = '';
					}
					?>
					<input type="hidden" name="email_enable" value="0">
					<input type="checkbox" id="email_enable_<?php echo esc_html($w_data->id); ?>" name="email_enable" data-id="<?php echo esc_html($w_data->id); ?>" class="tgl tgl-flat-sre enable_status_list" <?php echo esc_html($checked); ?> value="<?php echo esc_html($w_data->email_enable); ?>"/>
					<label class="tgl-btn" for="email_enable_<?php echo esc_html($w_data->id); ?>"></label>
					<span class="report-title" style="padding-right: 5px;">
						<strong style="margin:0">
							<?php if (empty($w_data->report_name)) { ?>
							<a class="row-title" href="admin.php?page=sre_customizer&type=report_options&id=<?php echo esc_html($w_data->id); ?>">
								<?php echo esc_html(stripslashes( '(no title)' )); ?>
							</a>
							<?php } else { ?>
							<a class="row-title" href="admin.php?page=sre_customizer&type=report_options&id=<?php echo esc_html($w_data->id); ?>">
								<?php echo esc_html(stripslashes( $w_data->report_name )); ?>
							</a>
							<?php } ?>
						</strong>
						<span class="report-next-run-date">
						<?php
						if ( 'one-time' == $w_data->email_interval) {
							echo '(';
							esc_html_e( 'Run', 'sales-report-email-pro' );
							echo ' - ';
							esc_html_e( $this->next_run_date($w_data), 'sales-report-email-pro' );
							echo ')';
						} else {
							if ( '1' == $w_data->email_enable && !empty($this->next_run_date($w_data)) ) { 
								echo '(';
								esc_html_e( 'Next Run Date', 'sales-report-email-pro' );
								echo ' - ';
								esc_html_e( gmdate('M d, Y g:iA', strtotime($this->next_run_date($w_data))), 'sales-report-email-pro' );
								echo ')';
							}
						}
						
						?>
						</span>
					</span>
					<span class="report-action">
							<a href="admin.php?page=sre_customizer&type=report_options&id=<?php echo esc_html($w_data->id); ?>" class="edit">
								<span class="dashicons dashicons-admin-generic"></span><?php //esc_html_e( 'Edit', 'sales-report-email-pro' ); ?></a>
							<a onclick="return confirm( 'Are you sure you want to delete this entry?' );" href="admin.php?page=<?php echo esc_html($this->screen_id); ?>&amp;action=delete&amp;id=<?php echo esc_html($w_data->id); ?>" class="trash">
								<span class="dashicons dashicons-trash"></span><?php //esc_html_e( 'Delete', 'sales-report-email-pro' ); ?>
							</a>
					</span>
				</div>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="report-tab asre-edit asre-btn">
			<a href="admin.php?page=sre_customizer&type=report_options&id=0" class="button-primary create_new_report <?php echo ( 'edit' === $tab ) ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Add Report', 'sales-report-email-pro' ); ?> +
			</a>
		</div> 
		<br/>
		<?php endif; ?>
	</div>
</section>
<?php
