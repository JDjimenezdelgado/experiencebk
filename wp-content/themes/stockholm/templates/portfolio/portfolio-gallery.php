<?php
//get global variables
global $wp_query;
global $qode_options;
global $wpdb;

//init variables
$portfolio_images 			= get_post_meta(get_the_ID(), "qode_portfolio_images", true);
$lightbox_single_project 	= 'no';
$columns_number 			= 'v4';

//is lightbox turned on for single project?
if (isset($qode_options['lightbox_single_project'])) {
	$lightbox_single_project = $qode_options['lightbox_single_project'];
}

//is lightbox turned on for video single project?
if (isset($qode_options['lightbox_video_single_project'])) {
	$lightbox_video_single_project = $qode_options['lightbox_video_single_project'];
}

//sort portfolio images by user defined input
if (is_array($portfolio_images)){
	usort($portfolio_images, "comparePortfolioImages");
}

if(get_post_meta(get_the_ID(), "qode_choose-number-of-portfolio-columns", true) !== "") {
	$columns_number = 'v'.get_post_meta(get_the_ID(), "qode_choose-number-of-portfolio-columns", true);
} elseif(isset($qode_options['portfolio_columns_number']) && $qode_options['portfolio_columns_number'] !== '') {
	$columns_number = 'v'.$qode_options['portfolio_columns_number'];
}

?>

<div class="portfolio_gallery">
	<?php
    $portfolio_m_images = get_post_meta(get_the_ID(), "qode_portfolio-image-gallery", true);
    if ($portfolio_m_images){
        $portfolio_image_gallery_array=explode(',',$portfolio_m_images);
        foreach($portfolio_image_gallery_array as $gimg_id){
            $title = get_the_title($gimg_id);
            $alt = get_post_meta($gimg_id, '_wp_attachment_image_alt', true);
            $portfolio_gallery_thumb_size = get_post_meta(get_the_ID(), 'qode_choose-portfolio-image-size', true);
			$portfolio_gallery_thumb_size = (!empty($portfolio_gallery_thumb_size)) ? get_post_meta(get_the_ID(), 'qode_choose-portfolio-image-size', true) : 'full';
            $image_src = wp_get_attachment_image_src( $gimg_id, $portfolio_gallery_thumb_size );
            if (is_array($image_src)) $image_src = $image_src[0];

            $image_light_src = wp_get_attachment_image_src( $gimg_id, 'full' );
            if (is_array($image_light_src)) $image_light_src = $image_light_src[0];
            ?>
            <?php if($lightbox_single_project == "yes"){ ?>
                <a class="lightbox_single_portfolio <?php echo $columns_number; ?>" title="<?php echo $title; ?>" href="<?php echo $image_light_src; ?>" data-rel="prettyPhoto[single_pretty_photo]">
                    <span class="gallery_text_holder"><span class="gallery_text_inner"><h4><?php echo $title; ?></h4></span></span>
                    <img src="<?php echo $image_src; ?>" alt="<?php echo $alt; ?>" />
                </a>
            <?php } else { ?>
                <a class="lightbox_single_portfolio <?php echo $columns_number; ?>" href="#">
                    <span class="gallery_text_holder"><span class="gallery_text_inner"><h4><?php echo $title; ?></h4></span></span>
                    <img src="<?php echo $image_src; ?>" alt="<?php echo $alt; ?>" />
                </a>
            <?php }
        }
    }

    if (is_array($portfolio_images) && count($portfolio_images)){
		foreach($portfolio_images as $portfolio_image){
			?>

			<?php if($portfolio_image['portfolioimg'] != ""){ ?>
				<?php

				list($id, $title, $alt) = qode_get_portfolio_image_meta($portfolio_image['portfolioimg']);
				
				$single_image_id = qode_get_attachment_id_from_url($portfolio_image['portfolioimg']);
				if(!empty($single_image_id)){
					$single_image_gallery_thumb_size = get_post_meta(get_the_ID(), 'qode_choose-portfolio-image-size', true);
					$single_image_size = (!empty($single_image_gallery_thumb_size)) ? get_post_meta(get_the_ID(), 'qode_choose-portfolio-image-size', true) : 'full';
		            $single_image_src = wp_get_attachment_image_src( $single_image_id, $single_image_size );
		            if (is_array($single_image_src)) $single_image_src = $single_image_src[0];
				} else {
					$single_image_src = stripslashes($portfolio_image['portfolioimg']);
				}

				?>
				<?php if($lightbox_single_project == "yes"){ ?>
					<a class="lightbox_single_portfolio <?php echo $columns_number; ?>" title="<?php echo $portfolio_image['portfoliotitle']; ?>" href="<?php echo stripslashes($portfolio_image['portfolioimg']); ?>" data-rel="prettyPhoto[single_pretty_photo]">
						<span class="gallery_text_holder"><span class="gallery_text_inner"><h4><?php echo $portfolio_image['portfoliotitle']; ?></h4></span></span>
						<img src="<?php echo $single_image_src; ?>" alt="<?php echo $alt; ?>" />
					</a>
				<?php } else { ?>
					<a class="lightbox_single_portfolio <?php echo $columns_number; ?>" href="#">
						<span class="gallery_text_holder"><span class="gallery_text_inner"><h4><?php echo $portfolio_image['portfoliotitle']; ?></h4></span></span>
						<img src="<?php echo $single_image_src; ?>" alt="<?php echo $alt; ?>" />
					</a>
				<?php } ?>

			<?php }else{ ?>

				<?php
				$portfolio_video_type = "";
				if (isset($portfolio_image['portfoliovideotype'])) $portfolio_video_type = $portfolio_image['portfoliovideotype'];
				switch ($portfolio_video_type){
					case "youtube": ?>
						<?php if($lightbox_video_single_project == "yes"){ ?>
							<?php
								$vidID = $portfolio_image['portfoliovideoid'];  
							    $thumbnail = "http://img.youtube.com/vi/".$vidID."/maxresdefault.jpg";
							?>
							<a class="lightbox_single_portfolio <?php echo $columns_number; ?>" title="<?php echo $portfolio_image['portfoliotitle']; ?>" href="http://www.youtube.com/watch?feature=player_embedded&v=<?php echo $vidID; ?>" rel="prettyPhoto[single_pretty_photo]">
								<i class="fa fa-play"></i>
								<img width="100%" src="<?php echo $thumbnail; ?>"></img>
							</a>
						<?php } else { ?>
							<a class="lightbox_single_portfolio <?php echo $columns_number; ?>" href="#">
								<iframe width="100%" src="http://www.youtube.com/embed/<?php echo $portfolio_image['portfoliovideoid'];  ?>?wmode=transparent" wmode="Opaque" frameborder="0" allowfullscreen></iframe>
							</a>
						<?php } ?>
						<?php	break;
					case "vimeo": ?>
						<?php if($lightbox_video_single_project == "yes"){ ?>
							<?php
								$vidID = $portfolio_image['portfoliovideoid'];
							    $xml = unserialize(@file_get_contents("http://vimeo.com/api/v2/video/$vidID.php"));
							    $thumbnail = $xml[0]['thumbnail_large'];  
							    $video_title = $xml[0]['title'];
							?>
							<a class="lightbox_single_portfolio <?php echo $columns_number; ?>" title="<?php echo $portfolio_image['portfoliotitle']; ?>" href="http://vimeo.com/<?php echo $portfolio_image['portfoliovideoid']; ?>" rel="prettyPhoto[single_pretty_photo]">
								<i class="fa fa-play"></i>
								<img width="100%" src="<?php echo $thumbnail; ?>"></img>
							</a>
						<?php } else { ?>
							<a class="lightbox_single_portfolio <?php echo $columns_number; ?>" href="#">
								<iframe src="http://player.vimeo.com/video/<?php echo $portfolio_image['portfoliovideoid']; ?>?title=0&amp;byline=0&amp;portrait=0" width="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
							</a>
						<?php } ?>
						<?php break;
					case "self": ?>
						<a class="lightbox_single_portfolio <?php echo $columns_number; ?>" onclick='return false;' href="#">
							<div class="video">
								<div class="mobile-video-image" style="background-image: url(<?php echo $portfolio_image['portfoliovideoimage']; ?>);"></div>
								<div class="video-wrap"  >
									<video class="video" poster="<?php echo $portfolio_image['portfoliovideoimage']; ?>" preload="auto">
										<?php if(!empty($portfolio_image['portfoliovideowebm'])) { ?> <source type="video/webm" src="<?php echo $portfolio_image['portfoliovideowebm']; ?>"> <?php } ?>
										<?php if(!empty($portfolio_image['portfoliovideomp4'])) { ?> <source type="video/mp4" src="<?php echo $portfolio_image['portfoliovideomp4']; ?>"> <?php } ?>
										<?php if(!empty($portfolio_image['portfoliovideoogv'])) { ?> <source type="video/ogg" src="<?php echo $portfolio_image['portfoliovideoogv']; ?>"> <?php } ?>
										<object width="320" height="240" type="application/x-shockwave-flash" data="<?php echo get_template_directory_uri(); ?>/js/flashmediaelement.swf">
											<param name="movie" value="<?php echo get_template_directory_uri(); ?>/js/flashmediaelement.swf" />
											<param name="flashvars" value="controls=true&file=<?php echo $portfolio_image['portfoliovideomp4']; ?>" />
											<img src="<?php echo $portfolio_image['portfoliovideoimage']; ?>" width="1920" height="800" title="No video playback capabilities" alt="Video thumb" />
										</object>
									</video>
								</div>
							</div>
						</a>
					<?php break;
				}
			}
		}
	}
	?>
</div>
<div class="two_columns_75_25 clearfix portfolio_container">
	<div class="column1">
		<div class="column_inner">
			<div class="portfolio_single_text_holder">
				<h2 class="portfolio_single_text_title"><span><?php the_title(); ?></span></h2>
				<?php the_content(); ?>
			</div>
		</div>
	</div>
	<div class="column2">
		<div class="column_inner">
			<div class="portfolio_detail">
				<?php
					//get portfolio custom fields section
					get_template_part('templates/portfolio/parts/portfolio-custom-fields');

					//get portfolio date section
					get_template_part('templates/portfolio/parts/portfolio-date');

					//get portfolio categories section
					get_template_part('templates/portfolio/parts/portfolio-categories');

					//get portfolio tags section
					get_template_part('templates/portfolio/parts/portfolio-tags');

					//get portfolio share section
					get_template_part('templates/portfolio/parts/portfolio-social');
				?>
			</div>
		</div>
	</div>
</div>