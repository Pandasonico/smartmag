<?php
/**
 * Default Page Template
 */

$props = isset($props) ? $props : [];
$props = array_replace([
	'content_class' => 'post-content page-content entry-content',
	'breadcrumbs'   => !Bunyad::posts()->meta('hide_breadcrumbs'),
	'wrap_classes'  => [],
], $props);

get_header();

// Spacious style classes.
$spacious_style = Bunyad::posts()->meta('page_spacious');
if ($spacious_style) {	
	if (Bunyad::core()->get_sidebar() === 'none') {
		$props['wrap_classes'][] = 'the-post-modern';
	}

	$props['content_class'] .= Bunyad::core()->get_sidebar() === 'none' ? 'content-spacious-full' : 'content-spacious';
}

if ($props['breadcrumbs']) {
	Bunyad::blocks()->load('Breadcrumbs')->render();
}

if (Bunyad::posts()->meta('featured_slider')):
	get_template_part('partials/featured-area');
endif;

?>

<div <?php Bunyad::markup()->attribs('main'); ?>>
	<?php if (apply_filters('bunyad_do_partial_page', true)): ?>
		<div class="ts-row">
			<div class="col-8 main-content">
				
				<?php if (have_posts()): the_post(); endif; // load the page ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class($props['wrap_classes']); ?>>

				<?php if (Bunyad::posts()->meta('page_title') !== 'no'): ?>
				
					
					<header class="post-header">				

					<!-- cancellata completamente tutta la parte relativa alla pubblicazione dell'immagine featured in quanto non necessaria nelle mie pagine, pubblico immagini solo nei post -->
					
					<h1 class="main-heading the-page-heading entry-title"><?php the_title(); ?></h1>
					
					<!-- Aggiungo social share sotto titolo -->
					<?php
					 $titolo =   get_the_title();
					 $titolo =  rawurlencode(htmlentities($titolo, ENT_COMPAT, 'UTF-8'));
					 $collegamento = get_permalink();
					?>

				<div class="post-share post-share-b spc-social-colors  post-share-b3">
				<a aria-label="Stampa" href="javascript:window.print()" title="Stampa" rel="nofollow noopener" class="cf service service-lg">
				<i class="tsi tsi-print"></i><span class="label">Stampa</span></a>
			
				<a aria-label="Condividi via email" class="cf service s-email service-lg"  href="mailto:?subject=<?php echo $titolo; ?>&amp;body=<?php echo $collegamento; ?>"  target="_blank" rel="nofollow noopener">
				<i class="tsi tsi-tsi tsi-envelope-o"></i><span class="label">Email</span></a>
				
				<a aria-label="Condividi con WhatsApp" onclick="window.open(this.href,'targetWindow','toolbars=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=700,left=200,top=200');return false;" href="https://wa.me/?text=<?php echo $titolo; ?> <?php echo $collegamento; ?>" class="cf service s-whatsapp service-lg" title="Condividi con WhatsApp" target="_blank" rel="nofollow noopener">
				<i class="tsi tsi-tsi tsi-whatsapp"></i><span class="label">WhatsApp</span></a>
			
				<a aria-label="Condividi con Telegram" onclick="window.open(this.href,'targetWindow','toolbars=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=700,left=200,top=200');return false;" href="https://t.me/share/url?url=<?php echo $collegamento; ?>&amp;title=<?php echo $titolo; ?>" class="cf service s-telegram service-lg" title="Condividi con Telegram" target="_blank" rel="nofollow noopener"><i class="tsi tsi-tsi tsi-telegram"></i><span class="label">Telegram</span></a>
				
		          <a aria-label="Condividi su Linkedin" onclick="window.open(this.href,'targetWindow','toolbars=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=700,left=200,top=200');return false;" href="https://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo $collegamento; ?>&title=<?php echo $titolo; ?>" class="cf service s-linkedin service-lg" title="Condividi su LinkedIn" target="_blank" rel="nofollow noopener"><i class="tsi tsi-tsi tsi-linkedin"></i><span class="label">LinkedIn</span></a>
			
				<a aria-label="Condividi su Twitter" onclick="window.open(this.href,'targetWindow','toolbars=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=700,left=200,top=200');return false;" href="https://twitter.com/intent/tweet?url=<?php echo $collegamento; ?>&amp;text=<?php echo $titolo; ?>" class="cf service s-twitter service-lg" title="Condividi su Twitter" target="_blank" rel="nofollow noopener"><i class="tsi tsi-tsi tsi-twitter"></i><span class="label">Twitter</span></a>
		
				<a aria-label="Condividi su Facebook" onclick="window.open(this.href,'targetWindow','toolbars=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=700,left=200,top=200');return false;" href="https://www.facebook.com/sharer.php?u=<?php echo $collegamento; ?>" class="cf service s-facebook service-lg" title="Condividi su Facebook" target="_blank" rel="nofollow noopener"><i class="tsi tsi-tsi tsi-facebook"></i><span class="label">Facebook</span></a>
			
				<a aria-label="Condividi su Reddit" onclick="window.open(this.href,'targetWindow','toolbars=0,location=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=700,left=200,top=200');return false;" href="https://www.reddit.com/submit?url=<?php echo $collegamento; ?>&amp;title=<?php echo $titolo; ?>" class="cf service s-reddit service-sm" title="Condividi su Reddit" target="_blank" rel="nofollow noopener"><i class="tsi tsi-tsi tsi-reddit-alien"></i><span class="label">Reddit</span></a>
			
				<a href="#" class="show-more" title="Mostra altri social"><i class="tsi tsi-share"></i></a>
		
				</div>
				</header><!-- .post-header -->
				<?php endif; ?>

				
			
					<div class="<?php echo esc_attr($props['content_class']); ?>">				
						<?php Bunyad::posts()->the_content(); ?>
						<?php get_template_part('partials/content/social-share'); ?>
					</div>

				</div>
				
			</div>
			
			<?php Bunyad::core()->theme_sidebar(); ?>
			
		</div> <!-- .row -->
	<?php endif; ?>
</div> <!-- .main -->

<?php get_footer(); ?>
