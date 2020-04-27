<div class="content post-editing-form">
	<div class="user-heading">
		<img src="{$me.avatar}" class="img-circle">
		<span>{$me.username}</span>
		<svg class="feather feather-arrow" width="18" height="18" viewBox="0 0 48 48" fill="#757575"><path d="M20 34l10-10 -10-10z"></path></svg>
		<span class="pp_area"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-video"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg> {lang('video')}</span>
		<div id="pp_loader">
		<span style="float:left;margin-right:60px;margin-top:-10px;">0%</span>
			<div class="speeding_wheel"></div>
		</div>
	</div>
  	<form class="form" id="upload-post-video" action="{$site_url}/aj/posts/upload-post-video">
		  <div class="form-group">
      	<textarea class="form-control" name="caption" rows="3" placeholder="{lang('add_post_caption')}"></textarea>
      </div>
      {if $context.challenge}
      <div class="form-group pp_mat_select">
        <label class="pp_mat_label">{lang('select_challenge')}</label>
        <div> 
          <select name="challenge" class="form-control" id="challenge"> 
            {foreach $context.challenge as $challenge}  
            <option value="{$challenge.id}" title="{$challenge.info}"> {$challenge.name} (Win Upto: {$context.currency_symbol} {$challenge.winner_prize}) </option> 
            {/foreach} 
          </select>
        </div>
        <div class="clear"></div>
      </div>
      {/if}  
  		<div class="form-group">
	  		<div class="form-control selecet-file-control" onclick="$('#post-video').trigger('click');">
  				<button class="btn btn-info" type="button">{lang('select_file')}</button>
  				<span class="video-file-name">{lang('no_file_choosen')}</span>
  				<div id="post_video_prnt" class="hidden embed-responsive embed-responsive-16by9">
  					<video id="post__video" controls></video>
  				</div>
        </div>
		  </div>
    	<div class="form-group publish">
    		<button type="submit" class="btn btn-info">{lang('publish')}</button>
    		<button type="reset" class="btn btn-default" id="close-anim-modal">{lang('close')}</button>
    	</div>
    	<input class="hidden" type="file" name="video" id="post-video" accept="video/*">
    	<input type="hidden" name="hash" value="{$csrf_token}">
  	</form>
</div>
{include file="js/script.upload.video.tpl" assign="script"}
{$script|minify_js}
