<div class="footer clearfix pp_side_footer">
	<div class="pull-right">
		<div class="footer-powered">
			<p>&copy; 2018 {$config.site_name}</p>
		</div>
	</div>
	<ul class="nav">
		<li class="dropup" style="padding-right: 0px;">
			<span class="dropdown-toggle" data-toggle="dropdown">
				<a><svg fill="#929292" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" class="feather feather-translate" style="margin-top: -3px;width: 15px;height: 15px;"><path d="M12.87,15.07L10.33,12.56L10.36,12.53C12.1,10.59 13.34,8.36 14.07,6H17V4H10V2H8V4H1V6H12.17C11.5,7.92 10.44,9.75 9,11.35C8.07,10.32 7.3,9.19 6.69,8H4.69C5.42,9.63 6.42,11.17 7.67,12.56L2.58,17.58L4,19L9,14L12.11,17.11L12.87,15.07M18.5,10H16.5L12,22H14L15.12,19H19.87L21,22H23L18.5,10M15.88,17L17.5,12.67L19.12,17H15.88Z"></path></svg> {lang('language')}</a>
			</span>
			<ul class="dropdown-menu">
				{foreach $langs as $key => $val}
					<li><a href='?lang={$key}'>{$val}</a></li>
				{/foreach}
			</ul>
		</li>
	</ul>
	<ul class="nav">
		<li><a href="{pxp_link('terms-of-use')}">{lang('terms')}</a></li>
		<li><a href="{pxp_link('privacy-and-policy')}">{lang('privacy_and_policy')}</a></li>
		<li><a href="{pxp_link('about-us')}">{lang('about')}</a></li>
	</ul>
</div>