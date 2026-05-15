<?php
/**
 * Site Setup
 *
 * Auto-creates the pages this theme expects (Home, About, CV, Projects, …),
 * binds them to the right page templates, configures Settings → Reading,
 * and builds the Primary nav menu.
 *
 * Runs once on theme activation. Idempotent — safe to re-run via the
 * Tools → Site Setup admin page (it skips pages that already exist).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Pages this theme expects. Order here = order in the nav menu.
 *
 * 'slug'     => [
 *   'title'    => Page title (also reused as nav label),
 *   'template' => Page template file ('' for default),
 *   'role'     => 'front' | 'posts' | null,
 *   'in_menu'  => Whether to add to the Primary menu,
 * ]
 */
function stevebaron_expected_pages(): array {
	return [
		'home'     => [ 'title' => 'Home',     'template' => '',                   'role' => 'front', 'in_menu' => true  ],
		'about'    => [ 'title' => 'About',    'template' => 'page-about.php',     'role' => null,    'in_menu' => true  ],
		'cv'       => [ 'title' => 'CV',       'template' => 'page-cv.php',        'role' => null,    'in_menu' => true  ],
		'projects' => [ 'title' => 'Projects', 'template' => 'page-projects.php',  'role' => null,    'in_menu' => true  ],
		'writing'  => [ 'title' => 'Writing',  'template' => '',                   'role' => 'posts', 'in_menu' => true  ],
		'photos'   => [ 'title' => 'Photos',   'template' => 'page-photos.php',    'role' => null,    'in_menu' => true  ],
		'now'      => [ 'title' => 'Now',      'template' => 'page-now.php',       'role' => null,    'in_menu' => true  ],
		'contact'  => [ 'title' => 'Contact',  'template' => 'page-contact.php',   'role' => null,    'in_menu' => true  ],
		'weather'  => [ 'title' => 'Weather',  'template' => 'page-weather.php',   'role' => null,    'in_menu' => false ],
	];
}

/**
 * Run the full setup. Returns a per-slug status map:
 *   'created' | 'existed' | 'template-updated' | 'error'
 */
function stevebaron_run_site_setup(): array {
	$pages   = stevebaron_expected_pages();
	$status  = [];
	$ids     = [];

	foreach ( $pages as $slug => $cfg ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			$status[ $slug ] = 'existed';

			// If the page exists but has no/wrong template, fix that.
			if ( $cfg['template'] ) {
				$current_template = get_post_meta( $existing->ID, '_wp_page_template', true );
				if ( $current_template !== $cfg['template'] ) {
					update_post_meta( $existing->ID, '_wp_page_template', $cfg['template'] );
					$status[ $slug ] = 'template-updated';
				}
			}
			continue;
		}

		$page_id = wp_insert_post( [
			'post_title'   => $cfg['title'],
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '',
		], true );

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			$status[ $slug ] = 'error';
			continue;
		}

		if ( $cfg['template'] ) {
			update_post_meta( $page_id, '_wp_page_template', $cfg['template'] );
		}
		$ids[ $slug ]   = $page_id;
		$status[ $slug ] = 'created';
	}

	// ── Settings → Reading: static front + posts page ────────────────────
	if ( isset( $ids['home'], $ids['writing'] ) ) {
		update_option( 'show_on_front',  'page' );
		update_option( 'page_on_front',  $ids['home'] );
		update_option( 'page_for_posts', $ids['writing'] );
	}

	// ── Primary menu ─────────────────────────────────────────────────────
	$menu_name = 'Primary';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

	if ( $menu_id && ! is_wp_error( $menu_id ) ) {
		$existing_items = wp_get_nav_menu_items( $menu_id ) ?: [];
		$existing_page_ids = array_map(
			fn( $item ) => (int) $item->object_id,
			array_filter( $existing_items, fn( $i ) => $i->object === 'page' )
		);

		foreach ( $pages as $slug => $cfg ) {
			if ( ! $cfg['in_menu'] || empty( $ids[ $slug ] ) ) continue;
			if ( in_array( (int) $ids[ $slug ], $existing_page_ids, true ) ) continue;

			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'     => $cfg['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $ids[ $slug ],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			] );
		}

		$locations = get_theme_mod( 'nav_menu_locations', [] );
		if ( empty( $locations['primary'] ) ) {
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	update_option( 'stevebaron_site_setup', current_time( 'mysql' ) );
	flush_rewrite_rules( false );

	return $status;
}

// Auto-run once on activation.
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'stevebaron_site_setup' ) ) {
		stevebaron_run_site_setup();
	}
} );

// ── Shared: convert a simple block-tree to Gutenberg-flavored HTML ──────────

/**
 * Takes an array of tuples [ 'p'|'h2'|'h3'|'list'|'quote', content ] and
 * returns Gutenberg-block HTML. Reused by every draft post inserter.
 */
function stevebaron_blocks_to_html( array $blocks ): string {
	$out = '';
	foreach ( $blocks as $block ) {
		[ $type, $content ] = $block;
		if ( $type === 'p' ) {
			$out .= "<!-- wp:paragraph -->\n<p>{$content}</p>\n<!-- /wp:paragraph -->\n\n";
		} elseif ( $type === 'h2' ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$content}</h2>\n<!-- /wp:heading -->\n\n";
		} elseif ( $type === 'h3' ) {
			$out .= "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">{$content}</h3>\n<!-- /wp:heading -->\n\n";
		} elseif ( $type === 'list' ) {
			$items = '';
			foreach ( (array) $content as $li ) {
				$items .= "<!-- wp:list-item -->\n<li>{$li}</li>\n<!-- /wp:list-item -->\n";
			}
			$out .= "<!-- wp:list -->\n<ul class=\"wp-block-list\">\n{$items}</ul>\n<!-- /wp:list -->\n\n";
		} elseif ( $type === 'quote' ) {
			$out .= "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>{$content}</p></blockquote>\n<!-- /wp:quote -->\n\n";
		}
	}
	return $out;
}

/**
 * Inserts a draft post idempotently. Returns the post ID (existing or new).
 *
 * @param array $args  Required keys: slug, title, excerpt, content, category, tags.
 */
function stevebaron_create_draft_post( array $args ): int {
	$existing = get_page_by_path( $args['slug'], OBJECT, 'post' );
	if ( $existing ) return (int) $existing->ID;

	$post_id = wp_insert_post( [
		'post_title'   => $args['title'],
		'post_name'    => $args['slug'],
		'post_status'  => 'draft',
		'post_type'    => 'post',
		'post_content' => $args['content'],
		'post_excerpt' => $args['excerpt'] ?? '',
	], true );
	if ( is_wp_error( $post_id ) || ! $post_id ) return 0;

	if ( ! empty( $args['category'] ) ) {
		$cat_id = get_cat_ID( $args['category'] );
		if ( ! $cat_id ) {
			$cat = wp_create_category( $args['category'] );
			if ( ! is_wp_error( $cat ) ) $cat_id = (int) $cat;
		}
		if ( $cat_id ) wp_set_post_categories( $post_id, [ $cat_id ] );
	}
	if ( ! empty( $args['tags'] ) ) wp_set_post_tags( $post_id, $args['tags'] );

	return (int) $post_id;
}

// ── FOX Weather launch draft post ────────────────────────────────────────────

/**
 * Returns the Gutenberg-formatted body of the FOX Weather launch essay.
 */
function stevebaron_fox_weather_post_content(): string {
	$paragraphs = [
		[ 'p',  "The morning of launch I was up at 4:30, like I had been every morning that week, watching the App Store rankings on a second monitor while waiting for the kettle. By 11 AM Eastern, FOX Weather was the #1 free app in the United States &mdash; ahead of TikTok, Instagram, Facebook, and every other app on every other phone in the country." ],
		[ 'p',  "If you'd told me at 23, standing in front of a chroma-key wall at WSBT in South Bend, Indiana, that the path from \"weekend meteorologist\" to \"#1 on the App Store\" was a thing that could happen, I would have laughed and asked you to also predict tomorrow's snow totals. But it did happen, and the path through it — almost three decades of broadcast, digital, product, and a long apprenticeship in not knowing what I didn't know — turned out to be exactly what the work needed." ],
		[ 'p',  "This is a longer post than I usually write. It's the story of how we built FOX Weather, what we got right, what almost broke us, and what I wish I'd known before we started." ],
		[ 'h2', "Fox Corporation's first new editorial brand in two decades" ],
		[ 'p',  "In February 2021, Fox Corporation asked me to come build the digital product organization for a new brand — not a spin-off, not a redesign, not a refresh. A net-new editorial property, with its own newsroom, its own meteorologists, its own visual identity, and a mandate to ship across mobile, web, livestream, VOD, OTT, and partner ecosystems. The first new brand Fox had launched in twenty years." ],
		[ 'p',  "I had two reactions to the offer. The first was \"yes, immediately.\" The second was \"we're going to need a bigger boat.\"" ],
		[ 'p',  "The mandate I wrote on the first day, and re-read every Monday for the next two years, was three sentences long:" ],
		[ 'list', [
			"Beat the legacy weather apps on day one.",
			"Be the best place to watch live weather coverage on any screen.",
			"Build the platform so the brand can grow into things that haven't been invented yet.",
		] ],
		[ 'p',  "Everything we did rolled up to one of those three sentences. When I had a hard product decision and an opinionated team and a deadline, I'd ask which of the three a given path served — and if the answer was \"none of them,\" we'd cut it. We cut a lot." ],
		[ 'h2', "The team" ],
		[ 'p',  "You cannot build a brand-new digital brand with a small team and a small clock. You can build one with a small team and a long clock, or a large team and a short clock, but the third option &mdash; small team, short clock &mdash; is a way to ship something that nobody uses." ],
		[ 'p',  "We went large team, short clock. Six months from product start to public launch, and an organization that hadn&#8217;t existed two years earlier &mdash; product, design, engineering, content operations, growth, analytics, all standing up at the same time. The principle we kept coming back to was the one I&#8217;d come back to in every product job since: hire people who have shipped something close to what you&#8217;re about to build. On purpose. Before the sprint cadence starts." ],
		[ 'p',  "The single biggest set of decisions I made that year were hiring decisions. Not product decisions. Not feature decisions. The senior hires set the bar, set the rhythm, and shaped everything that came after them. I&#8217;ve written more about that thinking <a href=\"/pick-the-team-first/\">in a separate post</a>." ],
		[ 'h2', "App Store Optimization, or how we became the most pre-ordered weather app at launch" ],
		[ 'p',  "The marketing strategy I argued for, and lost a few rounds on before I won the round that mattered, was this: we were going to win the App Store before we won the App Store." ],
		[ 'p',  "App Store Optimization is one of those phrases that sounds boring enough to skip past in a deck. It is, in fact, the difference between \"your brand-new app launches and nobody finds it\" and \"your brand-new app launches and is the most-downloaded app in the country.\" We took it seriously. We treated the app listing — the screenshots, the description, the keyword strategy, the icon — as a first-class editorial product. We A/B-tested everything we could test. We watched ranking signals like meteorologists watch dew point." ],
		[ 'p',  "The pre-order campaign was the other piece. We opened pre-orders months before launch, paired them with a content drumbeat — meteorologists, on-camera anchors, behind-the-scenes content — and ended up with <strong>two hundred and fifty thousand</strong> pre-orders on the day FOX Weather opened. Those pre-orders auto-downloaded on launch day. That single mechanic — a quarter of a million phones, all over the country, all auto-installing the app within the same six-hour window — is what put us at #1." ],
		[ 'p',  "We launched ahead of TikTok. It is a sentence I still sometimes say out loud and have to remind myself is true." ],
		[ 'h2', "Six months, full portfolio" ],
		[ 'p',  "Inside the six months we shipped:" ],
		[ 'list', [
			"<strong>iOS and Android apps</strong> with native mobile 3D radar — a serious technical lift, because doing 3D radar well on a phone is doing it in 4 dimensions when you count time and animating it in real time.",
			"<strong>The web product</strong>, designed to live alongside the app rather than mirror it. Different surface, different reader, different content velocity.",
			"<strong>24/7 livestream</strong>, the editorial flagship. Free, ad-supported, never behind any wall.",
			"<strong>A VOD library</strong> for the moments when the live show has just covered something extraordinary and you want to find it later.",
			"<strong>A year-ahead forecast</strong> — controversial inside the building, beloved outside it, and something I'd argue is one of the most quietly innovative features we shipped.",
			"<strong>An API infrastructure</strong> that, at peak, was handling roughly a billion requests per month.",
		] ],
		[ 'p',  "Doing all of that in six months means making decisions in fifteen minutes that you'd normally take three weeks for. It means having the right team. It means having a clear mandate. It means cutting things, including good things, because shipping a smaller product on time is worth more than shipping a bigger product late." ],
		[ 'h2', "Distribution" ],
		[ 'p',  "The other thing I'll say about that year, because it doesn't get talked about enough: the app was the product, but it wasn't the only product. We built SEO and content distribution across Google News, Yahoo News, Apple News, NewsBreak, SmartNews, and a few partner channels I won't name. Our SEO-optimized organic growth ran 80% year over year for the back half of my tenure. That kind of growth at that kind of scale is not luck. It's a strategy. We treated discovery on every surface — app store, search, news aggregators, social — as part of the same job. Because, for a consumer brand in 2021, it was." ],
		[ 'h2', "What I'd do again" ],
		[ 'p',  "The thing I'd do again, the thing I'd argue for every time, is the team. Pick the team first. Argue for the budget the team needs, even if the budget gets you in trouble. Be willing to lose a smaller fight to win the team fight. Everything else is downstream of the people you put in the room." ],
		[ 'p',  "The other thing I'd do again is the mandate. Three sentences. Re-read every Monday. Use as the answer to every roadmap question." ],
		[ 'h2', "What I'd do differently" ],
		[ 'p',  "We launched the editorial product, the app, the livestream, the VOD library, and the API in six months. I would not do that again on a six-month clock unless I had to. Some of those products would have been better with another quarter of incubation. I think we got away with it, but I think we got away with it on the strength of a remarkable team rather than a remarkable plan." ],
		[ 'p',  "I'd also have shipped fewer features at launch and more features at month three. The launch carried weight because it was a launch. Once you've launched, the second hill — proving people will come back tomorrow — is the actual hill. I'd have invested more in that hill earlier." ],
		[ 'h2', "What came next" ],
		[ 'p',  "A Webby Award for Visual Storytelling in 2023, which I am still proud of. A team that retained, almost to a person, through the next two years. An editorial brand that, last I checked, was still running, still hiring, and still hitting the App Store top charts on weather days." ],
		[ 'p',  "I left in May of 2023 to start doing something I'd been quietly wanting to do for years: advising other product and AI companies on what comes before launch. Two years in, that's the work I'm doing every day, and it's an enormous amount of fun." ],
		[ 'p',  "The path from chroma-key wall to #1 on the App Store is long. The path between any two adjacent stops is short. Most of the people I respect most in this work are people who took the next short step, and the next one, and the next one, and then looked up one day to find that they'd ended up somewhere they couldn't have predicted from the start." ],
		[ 'p',  "That's the post. There are probably six other posts inside it, and over the next few months I'll write some of them. If there's a piece of this you'd like me to expand on, <a href=\"/contact/\">say hi</a> — I read everything." ],
		[ 'p',  "— Steve" ],
	];

	return stevebaron_blocks_to_html( $paragraphs );
}

function stevebaron_create_fox_weather_draft(): int {
	return stevebaron_create_draft_post( [
		'slug'     => 'how-we-built-fox-weather-to-1',
		'title'    => 'How We Built FOX Weather to #1',
		'excerpt'  => "Notes on launching Fox Corporation's first new editorial brand in two decades — the team, the bets, and the launch day we ended up ahead of TikTok on the App Store.",
		'content'  => stevebaron_fox_weather_post_content(),
		'category' => 'Product',
		'tags'     => [ 'FOX Weather', 'product', 'launch', 'App Store' ],
	] );
}

// ── Other draft posts ────────────────────────────────────────────────────────

function stevebaron_eval_problem_post_content(): string {
	return stevebaron_blocks_to_html( [
		[ 'p',  "The single most consistent thing I see when I sit down with an AI team early in a project is this: they can ship a model change in an afternoon, but they can&#8217;t tell you whether the change made things better." ],
		[ 'p',  "That gap &mdash; between <em>we changed it</em> and <em>we know it&#8217;s better</em> &mdash; is where most AI products quietly die." ],
		[ 'h2', "The “looks good to me” trap" ],
		[ 'p',  "Almost every new AI feature I get pulled into begins life in roughly the same way. A small team builds something, the demo is good, leadership funds it, and a year later the product has an organic users-love-it story sitting next to an unsolved we-can&#8217;t-explain-why-it-degrades story." ],
		[ 'p',  "Underneath, the workflow looks like this: someone changes a prompt, runs five queries through it, eyeballs the responses, says “yeah, that&#8217;s better,” and ships. The team I&#8217;m watching is rigorous about a hundred other things. They&#8217;re not rigorous about this one because the alternative &mdash; a real eval setup &mdash; is significantly harder than it sounds." ],
		[ 'h2', "Why it’s hard" ],
		[ 'p',  "A good eval setup needs four things, all of them annoying:" ],
		[ 'list', [
			'<strong>A representative dataset.</strong> Not the queries you wish your users were sending, the queries they’re actually sending. Building this dataset is a labeling and cleaning project, not an engineering one, and it never finishes.',
			'<strong>An automated grader.</strong> Either an LLM-as-judge, a programmatic rule set, or human raters &mdash; but something that scales past the team eyeballing fifty examples per change.',
			'<strong>Manual spot checks alongside the automated number.</strong> Because the automated grader will be wrong in ways you don’t see coming, and the cheapest signal that something’s gone sideways is still “the model started saying something weird and a human noticed.”',
			'<strong>A regression flag that fires loudly.</strong> When a change improves your top-line eval score but tanks one of your four most important subcategories, you need that to land in the right person’s inbox before the change ships, not after.',
		] ],
		[ 'p',  "That&#8217;s not infrastructure that a fast-moving early-stage team naturally builds. It looks like overhead. It feels like overhead. It is overhead. And then you ship a regression to all of your users and you spend three weeks rolling it back and you remember why it was worth it." ],
		[ 'h2', "What the better teams do" ],
		[ 'p',  "The pattern in the small handful of teams who handle this well is the same in every case: they treat evals as a product, not as a testing concern." ],
		[ 'p',  "They have a person whose job is evals. They have a private eval set that lives separately from prompts and code, gets reviewed and updated weekly, and is treated as a piece of company IP. They have a dashboard. They have a Slack channel where the dashboard&#8217;s red rows post themselves. They have a culture where saying “I&#8217;m not shipping this until the eval moves” is normal, not heroic." ],
		[ 'p',  "The good news is none of this requires money or seniority. The team I worked with that had the best eval setup I&#8217;ve seen had four engineers and one person on a part-time contract who owned the dataset. The hardest part was getting everyone to take the dataset seriously. The actual code was a hundred lines." ],
		[ 'h2', "What to do next week" ],
		[ 'p',  "If you&#8217;re an early-stage team without an eval setup, here&#8217;s the cheapest possible version:" ],
		[ 'list', [
			'Pick 50 queries from your real logs. Don’t overthink the sample.',
			'For each query, write down what a great response would include.',
			'For each new prompt or model change, run those 50 queries and compare.',
			'Whoever’s doing the change writes one paragraph explaining what’s better and one paragraph explaining what’s worse. That paragraph goes in the PR.',
		] ],
		[ 'p',  "That&#8217;s it. It&#8217;s not a research-grade setup. It will catch most of the worst regressions and most of the most embarrassing degradations. It costs an hour of labeling per fifty examples. It will save you weeks downstream." ],
		[ 'p',  "The product that&#8217;s a year ahead of you in your category is doing some version of this. You don&#8217;t need a more elaborate version &mdash; you need a started version." ],
		[ 'p',  '— Steve' ],
	] );
}

function stevebaron_create_eval_problem_draft(): int {
	return stevebaron_create_draft_post( [
		'slug'     => 'the-eval-problem',
		'title'    => 'The Eval Problem',
		'excerpt'  => "Changing a model is easy. Knowing whether you made it better is the actual job, and most teams haven't built the eval setup that question requires.",
		'content'  => stevebaron_eval_problem_post_content(),
		'category' => 'AI',
		'tags'     => [ 'AI', 'evals', 'RAG', 'product' ],
	] );
}

function stevebaron_pick_the_team_post_content(): string {
	return stevebaron_blocks_to_html( [
		[ 'p',  "Here&#8217;s a thing I believe and have been saying out loud for so long that I&#8217;m a little bored of saying it: when you have a clean sheet of paper and a fixed amount of capital, you should spend it on the team before you spend it on anything else." ],
		[ 'p',  "Not the technology. Not the user research. Not the brand work. Not the agency. Not even the office. The team." ],
		[ 'p',  "This is one of those statements that everybody nods along to and then quietly violates the next week. I&#8217;ve watched it happen at every company I&#8217;ve worked at, including ones I was leading. So I want to take a swing at why it&#8217;s hard, and what I&#8217;ve learned makes the team-first instinct stick." ],
		[ 'h2', "What I mean" ],
		[ 'p',  "When I say “pick the team first,” I mean two specific things." ],
		[ 'p',  "First, <em>do the hiring work before you let the project hit its sprint cadence.</em> Don&#8217;t start the work, realize a month in that you&#8217;re under-staffed at one position, and then try to backfill while shipping. By month two, the team is shaped around the gap, the gap is shaped around the team, and you&#8217;re never quite running at full speed again." ],
		[ 'p',  "Second, <em>spend more than you wanted to on the senior hires that will define the shape of everything else.</em> The lead engineer, the lead designer, the lead PM. These are the people who will hire your next ten people, set your bar, write your operating rhythm, and decide what gets cut. If you compromise on these roles, you compromise on everything downstream of them, forever." ],
		[ 'h2', "The compounding effect" ],
		[ 'p',  "A good senior hire doesn&#8217;t just get you one good person. It gets you the next set of people better, because senior hires recruit their networks. And it gets you the set after that, because the rhythm and the bar are set by the first round. By month six, the difference between a team that did the team-first work and a team that didn&#8217;t is enormous and almost impossible to close." ],
		[ 'h2', "Why this is hard" ],
		[ 'p',  "Three reasons, in roughly increasing order of importance." ],
		[ 'p',  "<strong>The budget conversation is uncomfortable.</strong> Senior hires cost more than the budget you have. You will have to ask for the budget to be moved. The person whose budget it is will not love this. You will have to win that conversation, sometimes more than once. Most leaders don&#8217;t win it because they don&#8217;t try as hard as the conversation requires." ],
		[ 'p',  "<strong>The timeline conversation is also uncomfortable.</strong> Senior hires take longer to land. Your CFO and your board are watching the ramp. Telling them “we&#8217;re going slower for the first quarter because I&#8217;m waiting for the right CTO” is a difficult sentence to say. It is also almost always the right sentence to say." ],
		[ 'p',  "<strong>You have to be honest about who&#8217;s “the right hire.”</strong> This is the one I see leaders get wrong most. The right hire is not the most-impressive-credentialed candidate. The right hire is the one who fits the <em>specific</em> shape of the work. A brilliant ex-FAANG engineer who has only ever worked on a thousand-person platform team can be exactly wrong for a five-person early-stage product. A scrappy generalist with mid-tier resume signal can be exactly right. You have to interview for the shape, not the brand on the resume." ],
		[ 'h2', "What “the right hire” looks like" ],
		[ 'p',  "A few signals I&#8217;ve learned to weight, in order:" ],
		[ 'list', [
			'<strong>They have shipped something close to what you’re trying to ship.</strong> Not perfectly the same thing. But close enough that their pattern-matching is going to fire correctly when something goes sideways. This is worth more than every other signal combined.',
			'<strong>You leave the conversation having learned something.</strong> If you walked out of the interview thinking the candidate is great and yourself is also great, you weren’t listening. The right senior hire is one whose thinking on the problem is better than yours in at least one important way.',
			'<strong>They make decisions in front of you.</strong> A candidate who can move from “let me think about that” to a clear position inside a thirty-minute conversation is a candidate who can lead. A candidate who hedges every answer is going to hedge with their team too.',
			'<strong>They turn down something you offer.</strong> If a candidate accepts everything you put in front of them &mdash; title, comp, scope, location, the whole package, no pushback &mdash; you’re either over-paying or hiring someone who’s going to take the same posture with the rest of their job.',
		] ],
		[ 'h2', "What I do now" ],
		[ 'p',  "These days, the engagements I&#8217;m most often pulled into are pre-launch product reviews for early-stage AI companies. The single most consistent question I&#8217;m asked is “what should we be doing right now to set this up for success?” And in seventy percent of cases, the honest answer is: <em>figure out who&#8217;s missing from the team, hire them before you do anything else, and stop trying to make the current team do the job the missing person is supposed to do.</em>" ],
		[ 'p',  "This usually doesn&#8217;t make me popular with the founder. The founder wants to talk about the product. I get it. But I&#8217;ve seen this play out enough times now that I&#8217;m willing to be the unpopular voice in the room for the thirty minutes it takes to make the point." ],
		[ 'p',  "Pick the team first. Everything else is downstream." ],
		[ 'p',  '— Steve' ],
	] );
}

function stevebaron_create_pick_the_team_draft(): int {
	return stevebaron_create_draft_post( [
		'slug'     => 'pick-the-team-first',
		'title'    => 'Pick the Team First',
		'excerpt'  => "The single highest-leverage decision in any product effort is the people you put in the room. Most leaders know this and still get it wrong, because the right team is rarely the cheapest team.",
		'content'  => stevebaron_pick_the_team_post_content(),
		'category' => 'Product',
		'tags'     => [ 'team', 'hiring', 'leadership', 'product' ],
	] );
}

function stevebaron_tribune_migration_post_content(): string {
	return stevebaron_blocks_to_html( [
		[ 'p',  "Somewhere on a hard drive I no longer have access to, there&#8217;s a slide I made in 2016 that opens with one sentence: <em>“the CMS is the single most expensive piece of software we own, and almost no one outside this room knows that.”</em>" ],
		[ 'p',  "The pitch in that deck was that Tribune Media should stop running its proprietary Rails CMS for its 40-station local TV digital network and move the whole portfolio onto WordPress VIP. The eventual answer was yes. The path between yes and done took about a year. The savings were close to 80% of the platform line. The audience grew. The team retained. I want to write a little of this down because I get asked about it more than I expected to." ],
		[ 'h2', "What we were starting with" ],
		[ 'p',  "A custom Rails CMS that had been built inside the company over the course of a decade. Forty-plus station websites running on it. Custom integrations to every weather and news and ad vendor in the building. A small engineering team responsible for keeping the whole thing alive, plus a much larger ops team responsible for the editorial workflow on top of it." ],
		[ 'p',  "The CMS worked. That&#8217;s the important thing to acknowledge up front. It served roughly 100 million monthly uniques. It supported live video. It had a custom WYSIWYG. It was not, by any reasonable measure, broken software." ],
		[ 'p',  "It was also, by any reasonable measure, the wrong software for the next five years. The team to maintain it was expensive. Every new vendor integration was a custom build. Every editorial feature request waited in a queue behind every operational fix. The engineers maintaining it were good engineers, but they were doing the work of three engineers each, and we couldn&#8217;t hire fast enough to catch up." ],
		[ 'p',  "We had also lost something subtle: control over our own roadmap. Every editorial improvement we wanted to make required platform engineering hours we didn&#8217;t have. We were running a content business and most of our engineering capacity went into keeping the lights on for a content system, not into improving the content business." ],
		[ 'h2', "Why WordPress VIP" ],
		[ 'p',  "I knew when I started writing the proposal that WordPress would be a controversial answer inside the building. WordPress had, and still has, a reputation problem in big media &mdash; “the thing your daughter&#8217;s blog runs on” &mdash; which is mostly unfair and is also mostly beside the point." ],
		[ 'p',  "The case I made was a four-line case:" ],
		[ 'list', [
			'<strong>The ops team will be more productive immediately.</strong> Most journalists and producers either already know WordPress or learn it in an afternoon. Our custom CMS took a week of training and a permanent help desk.',
			'<strong>The vendor ecosystem is enormous and free.</strong> Every weather widget, every ad partner, every analytics tool already has a WordPress integration that someone else maintains.',
			'<strong>WordPress VIP runs the platform, not us.</strong> Scaling, security, uptime, edge caching, DDoS protection &mdash; all of that becomes someone else’s job. Our small engineering team can focus on differentiating work.',
			'<strong>The cost line is dramatically lower.</strong> Replacing the proprietary stack and its associated headcount with the VIP contract was roughly an 80% reduction in run cost, even accounting for the migration spend.',
		] ],
		[ 'p',  "The fourth bullet was the bullet that closed the deal. The other three were what made it the right answer instead of just the cheap answer." ],
		[ 'h2', "The plan" ],
		[ 'p',  "A year, structured roughly like this:" ],
		[ 'list', [
			'<strong>Q1: One station, end-to-end.</strong> Pick a station nobody loves. Cut over their site to WordPress VIP. Solve every integration problem on that one site. Document everything. Train one editorial team.',
			'<strong>Q2: Five stations, in waves.</strong> Reuse everything from Q1. Test all the load conditions. Solve the SEO redirect problem at scale.',
			'<strong>Q3: Twenty stations.</strong> Production at speed. Editorial training in batches.',
			'<strong>Q4: The rest, plus the legacy decommission.</strong> Including the conversations with vendors whose Rails integrations were now permanently obsolete.',
		] ],
		[ 'p',  "The thing I am most proud of in retrospect is that we did not slip the schedule. We finished the quarter ahead of forecast on the run rate and shipped the last station three days early." ],
		[ 'h2', "What went well" ],
		[ 'p',  "A few things, mostly people-shaped:" ],
		[ 'list', [
			'The team. The migration leads on both the engineering and the editorial-ops sides were people who had been at the company long enough to know where every body was buried. You cannot do this work with new hires. You need institutional memory.',
			'The decision to invest in one station completely before opening the next one. The Q1 station took twelve weeks. We spent another four weeks just polishing it. By the time we started Q2, we had a template that worked. Every later station took two to three weeks.',
			'The migration toolchain. We built a single migration script that took the proprietary CMS&#8217;s content export, ran a transform pass, and wrote it directly into WordPress VIP. We tested the transform on three months of historical content per station before we cut over. Catastrophic edge cases were found in the test pass, not in production.',
			'The decision to write the editorial training as a self-paced video course rather than running in-person training for every station. The course was good. The stations took it in their own pace. The help desk volume on launch days was a quarter of what we expected.',
		] ],
		[ 'h2', "What I’d do differently" ],
		[ 'p',  "Three things, in roughly increasing order of regret:" ],
		[ 'p',  "<strong>We under-invested in the editorial features that WordPress doesn&#8217;t have out of the box.</strong> We assumed that since WordPress can do almost everything, the gaps could wait. The gaps could not wait. Local TV stations have specific editorial workflows &mdash; breaking-news cut-ins, weather alert templates, election-night live blogs &mdash; that needed bespoke work, and we started that work later than we should have." ],
		[ 'p',  "<strong>We were slow to deprecate the proprietary CMS.</strong> We kept it running in parallel for too long because it was the safety net. The safety net was expensive. Once a station migrated successfully, we should have moved on to retiring that station&#8217;s old environment within thirty days. We waited closer to ninety, on average. That choice cost real money." ],
		[ 'p',  "<strong>We didn&#8217;t tell the story externally well enough.</strong> This was an 80% cost reduction on a major platform line, done in a year, on a national publishing network. It should have been a press story. We told it internally; we did not tell it externally. I think there was a missed opportunity to recruit talent into the team on the strength of the work itself." ],
		[ 'h2', "The thing I think about most" ],
		[ 'p',  "In the years since, I&#8217;ve watched a lot of media companies wrestle with this same decision. Almost all of them eventually move to a more modern, more open, more vendor-rich CMS. Almost all of them take longer than they thought they would. Almost all of them save a lot of money on the way." ],
		[ 'p',  "The thing I wish someone had told me before we started: <em>the migration is the easy part.</em> The migration is mechanical. The hard part is the cultural shift inside the engineering and editorial teams from “we run our own everything” to “we participate in an ecosystem someone else runs.” That mindset shift takes longer than the codebase shift, and it&#8217;s the thing that determines whether you actually realize the value of the move." ],
		[ 'p',  "If you&#8217;re staring at a custom CMS and a flat cost line and a slow editorial workflow: it&#8217;s almost always the right move. Start with one site, take it all the way to done before you open the next one, and tell the story when you finish." ],
		[ 'p',  '— Steve' ],
	] );
}

function stevebaron_create_tribune_migration_draft(): int {
	return stevebaron_create_draft_post( [
		'slug'     => 'how-we-migrated-40-tv-stations-off-a-proprietary-cms-in-a-year',
		'title'    => 'How We Migrated 40 TV Stations Off a Proprietary CMS in a Year',
		'excerpt'  => "We took a 40-station national publishing platform off a custom Rails CMS, moved it onto WordPress VIP, and cut platform costs by about 80% in the process. Here's roughly how it went.",
		'content'  => stevebaron_tribune_migration_post_content(),
		'category' => 'Product',
		'tags'     => [ 'Tribune', 'WordPress', 'CMS', 'migration', 'product' ],
	] );
}

// ── About page content ───────────────────────────────────────────────────────

/**
 * Returns the Gutenberg-block body of the About page.
 */
function stevebaron_about_page_content(): string {
	$blocks = [
		[ 'p',  "I'm Steve. Product, AI &amp; digital transformation executive based in Salt Lake City. I spent six and a half years rebuilding the digital platform at Tribune Media, then two and a half years at Fox Corporation building FOX Weather from a whiteboard sketch to #1 on the US App Store. Now I advise consumer technology, AI, and digital platform companies on the work that comes before launch." ],

		[ 'h2', 'What I&#8217;m doing now' ],
		[ 'p',  "Mostly: advising. Pre-launch product reviews, applied AI workflows, GTM execution, launch readiness. The engagements over the last two years have spanned consumer electronics hardware, AI assistants (response-quality, supervised fine-tuning, RAG), and a category-leading multimodal AI product at one of the global tech companies. Some of it is technical product work; some of it is helping a senior team see the third-best option that&#8217;s actually the right answer." ],
		[ 'p',  "I&#8217;m based in Salt Lake City and I&#8217;m comfortable working remote, hybrid, or traveling as needed. Open to advisory, fractional, and full-time conversations." ],

		[ 'h2', 'How I got here' ],
		[ 'p',  "I started in 1993 as a weather anchor in Gainesville, Florida. The station hired me partly for my forecasting and partly because they needed someone who would also operate the camera. I did that for two years, then two years in South Bend, Indiana, then nine years in Salt Lake City as a meteorologist and reporter at FOX 13 News. I won an Emmy for Breaking News Coverage in 2006, which seemed like a reasonable cue to try something different." ],
		[ 'p',  "That same year I moved to Chicago for my first digital leadership role at Fox Television Stations. From there: VP of Digital Content &amp; Technology at Local TV, LLC (sold to Tribune for $2.7B in 2013); VP of Digital &amp; Head of Product and Engineering at Tribune Media (sold to Nexstar in 2019 for $7.2B); a year of post-acquisition integration work at Nexstar; a focused engagement as Chief Strategy Officer at the Local Media Association; and then Fox Corporation called and asked if I&#8217;d come build a brand from scratch." ],
		[ 'p',  "The FOX Weather years were a lot. I&#8217;ve written about that one <a href=\"/how-we-built-fox-weather-to-1/\">separately</a> — the short version is we shipped a full mobile, web, livestream, and VOD portfolio in six months, drove the app to #1 ahead of TikTok and Instagram on launch day, and earned a Webby Award for Visual Storytelling along the way. I left in May 2023 to start the advisory practice." ],

		[ 'h2', 'What I care about' ],
		[ 'p',  "A few things, on repeat:" ],
		[ 'list', [
			'<strong>Pick the team first.</strong> Argue for the budget the team needs, even if the budget gets you in trouble. Everything else is downstream of the people in the room.',
			'<strong>Cut things you love.</strong> A smaller product on time beats a bigger product late. Most of the worst meetings I&#8217;ve sat in were about features that should have been killed two months earlier.',
			'<strong>Distribution is part of the product.</strong> App Store Optimization, SEO, content distribution &mdash; they live in the same conversation as the product itself, not in a separate &#8220;marketing&#8221; track that gets booked the week before launch.',
			'<strong>Translation matters.</strong> A career of explaining radar imagery to a TV camera turns out to be a remarkably useful skill in a product review.',
		] ],

		[ 'h2', 'Outside the work' ],
		[ 'p',  "I live in Salt Lake City because the mountains are right there and the snow is real. The weather is still my favorite hobby &mdash; I have probably read more morning forecast discussions than is healthy. I bake bread, ski when I can, and spend a lot of time on trails with my family." ],

		[ 'h2', 'Where to find me' ],
		[ 'p',  "Email is best: <a href=\"mailto:steve@stevebaron.com\">steve@stevebaron.com</a>. I read everything and try to respond in a day or two. I&#8217;m also on <a href=\"https://linkedin.com/in/stevembaron\" target=\"_blank\" rel=\"noopener\">LinkedIn</a> &mdash; if you want a quick read on the executive arc, that&#8217;s the place. If you want to see what the team and I built at FOX Weather, the <a href=\"/how-we-built-fox-weather-to-1/\">launch essay</a> is a long but honest one." ],
		[ 'p',  "If you&#8217;re working on a launch, an AI product, or a category bet where the path is not obvious yet &mdash; I&#8217;d love to hear about it." ],
		[ 'p',  '— Steve' ],
	];

	$out = '';
	foreach ( $blocks as $block ) {
		[ $type, $content ] = $block;
		if ( $type === 'p' ) {
			$out .= "<!-- wp:paragraph -->\n<p>{$content}</p>\n<!-- /wp:paragraph -->\n\n";
		} elseif ( $type === 'h2' ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$content}</h2>\n<!-- /wp:heading -->\n\n";
		} elseif ( $type === 'list' ) {
			$items = '';
			foreach ( (array) $content as $li ) {
				$items .= "<!-- wp:list-item -->\n<li>{$li}</li>\n<!-- /wp:list-item -->\n";
			}
			$out .= "<!-- wp:list -->\n<ul class=\"wp-block-list\">\n{$items}</ul>\n<!-- /wp:list -->\n\n";
		}
	}
	return $out;
}

/**
 * Populates the About page with the canonical content. Skips the write
 * if the page already has content unless $force = true. Returns:
 *   'no-page'   — no About page exists
 *   'has-content' — page has content and force = false
 *   'updated'   — content (and excerpt) were written
 */
function stevebaron_populate_about_page( bool $force = false ): string {
	$about = get_page_by_path( 'about' );
	if ( ! $about ) return 'no-page';
	$current = trim( strip_tags( wp_strip_all_tags( $about->post_content ) ) );
	if ( $current !== '' && ! $force ) return 'has-content';

	$excerpt = "I've spent 25+ years moving ambiguous product, audience, and growth problems from impossible to shipped — most recently as the SVP who built FOX Weather to #1 on the US App Store. These days I'm advising the people doing similar work.";

	wp_update_post( [
		'ID'           => $about->ID,
		'post_content' => stevebaron_about_page_content(),
		'post_excerpt' => $excerpt,
	] );
	return 'updated';
}

// ── Now page content ────────────────────────────────────────────────────────

/**
 * Canonical "Now" snapshot data. Keys map to the _sb_now_* meta fields
 * defined in inc/meta-boxes.php.
 */
function stevebaron_now_page_data(): array {
	return [
		'_sb_now_working_on' => 'Pre-launch product reviews and applied-AI workflows for two AI assistant clients, plus an advisory engagement on a consumer-electronics roadmap. Quietly drafting a follow-up to the FOX Weather essay.',
		'_sb_now_reading'    => '“The Coming Wave” by Mustafa Suleyman, slowly. Daily forecast discussions out of the Salt Lake City NWS office, quickly.',
		'_sb_now_watching'   => 'The new season of Slow Horses. Whatever live coverage I can find when a winter storm rolls through the Wasatch.',
		'_sb_now_learning'   => 'How retrieval-augmented generation actually performs at scale — specifically the eval problem. Knowing whether a change made the model better is harder than people think.',
		'_sb_now_outside'    => 'Skiing Alta and Snowbird whenever the forecast lines up. Trail-running the Bonneville Shoreline when it doesn’t. Bread baking is the indoor version.',
		'_sb_now_yes_to'     => 'Advisory work, fractional executive roles, and full-time leadership conversations. Founders thinking about AI product readiness, audience growth, or launch GTM.',
		'_sb_now_no_to'      => 'Speculative speaking gigs without a topic. Cold outreach that doesn’t include what you’re working on.',
		'_sb_now_location'   => 'Salt Lake City',
	];
}

/**
 * Returns the Gutenberg body of the Now page (the preamble that renders
 * below the items). Short and editable.
 */
function stevebaron_now_page_content(): string {
	$p1 = "A snapshot of what I&#8217;m focused on right now. It changes. If you&#8217;re seeing this on a different month, the page may be stale &mdash; feel free to ask what&#8217;s actually happening.";
	return "<!-- wp:paragraph -->\n<p>{$p1}</p>\n<!-- /wp:paragraph -->\n";
}

/**
 * Populates the Now page. Writes each meta field if it's currently empty;
 * if $force is true, overwrites existing values too. Returns:
 *   'no-page'      — no Now page exists
 *   'all-filled'   — every meta field has content and force = false
 *   'updated'      — at least one field was written
 */
function stevebaron_populate_now_page( bool $force = false ): string {
	$now = get_page_by_path( 'now' );
	if ( ! $now ) return 'no-page';

	$data    = stevebaron_now_page_data();
	$written = 0;

	foreach ( $data as $key => $value ) {
		$existing = get_post_meta( $now->ID, $key, true );
		if ( $existing === '' || $force ) {
			update_post_meta( $now->ID, $key, $value );
			$written++;
		}
	}

	// Always refresh the "Last updated" stamp to today.
	$today_existing = get_post_meta( $now->ID, '_sb_now_updated', true );
	if ( $force || $today_existing === '' ) {
		update_post_meta( $now->ID, '_sb_now_updated', wp_date( 'F j, Y' ) );
		$written++;
	}

	// Populate page body if empty (or forced).
	$body_current = trim( wp_strip_all_tags( $now->post_content ) );
	if ( $body_current === '' || $force ) {
		wp_update_post( [
			'ID'           => $now->ID,
			'post_content' => stevebaron_now_page_content(),
		] );
		$written++;
	}

	if ( $written === 0 ) return 'all-filled';
	return 'updated';
}

// ── Admin page: Tools → Site Setup ───────────────────────────────────────

add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Site Setup (Steve Baron theme)', 'stevebaron' ),
		__( 'Site Setup', 'stevebaron' ),
		'manage_options',
		'stevebaron-setup',
		'stevebaron_setup_admin_page'
	);
} );

function stevebaron_setup_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$ran     = false;
	$reseeded = false;
	$status  = [];
	$reseed_result = null;

	if ( isset( $_POST['stevebaron_setup_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_setup_nonce'] ) ), 'stevebaron_setup' ) ) {
		$status = stevebaron_run_site_setup();
		$ran    = true;
	}

	if ( isset( $_POST['stevebaron_reseed_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_reseed_nonce'] ) ), 'stevebaron_reseed' ) ) {
		$reseed_result = stevebaron_reseed_content();
		$reseeded      = true;
	}

	// All shippable draft posts, in display order.
	$drafts = [
		[
			'key'      => 'fox',
			'nonce'    => 'stevebaron_fox',
			'title'    => 'How We Built FOX Weather to #1',
			'slug'     => 'how-we-built-fox-weather-to-1',
			'create'   => 'stevebaron_create_fox_weather_draft',
			'subject'  => __( 'FOX Weather launch essay', 'stevebaron' ),
			'desc'     => __( 'The story of building FOX Weather from a whiteboard to #1 on the US App Store. ~1,200 words.', 'stevebaron' ),
		],
		[
			'key'      => 'eval',
			'nonce'    => 'stevebaron_eval',
			'title'    => 'The Eval Problem',
			'slug'     => 'the-eval-problem',
			'create'   => 'stevebaron_create_eval_problem_draft',
			'subject'  => __( 'AI eval problem essay', 'stevebaron' ),
			'desc'     => __( 'Why most AI teams ship changes without knowing if they made things better, and what to do about it. ~700 words.', 'stevebaron' ),
		],
		[
			'key'      => 'team',
			'nonce'    => 'stevebaron_team',
			'title'    => 'Pick the Team First',
			'slug'     => 'pick-the-team-first',
			'create'   => 'stevebaron_create_pick_the_team_draft',
			'subject'  => __( 'Pick the team first essay', 'stevebaron' ),
			'desc'     => __( 'Expansion of the principle from the About page, with a FOX Weather hiring story. ~900 words.', 'stevebaron' ),
		],
		[
			'key'      => 'tribune',
			'nonce'    => 'stevebaron_tribune',
			'title'    => 'How We Migrated 40 TV Stations Off a Proprietary CMS in a Year',
			'slug'     => 'how-we-migrated-40-tv-stations-off-a-proprietary-cms-in-a-year',
			'create'   => 'stevebaron_create_tribune_migration_draft',
			'subject'  => __( 'Tribune CMS migration essay', 'stevebaron' ),
			'desc'     => __( 'The Rails → WordPress VIP migration that cut platform costs 80% across 40 stations. ~1,100 words.', 'stevebaron' ),
		],
	];

	$draft_results = []; // key => [ 'id' => int, 'created' => bool ]
	foreach ( $drafts as $d ) {
		if ( isset( $_POST[ $d['nonce'] . '_nonce' ] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $d['nonce'] . '_nonce' ] ) ), $d['nonce'] ) ) {
			$existing = get_page_by_path( $d['slug'], OBJECT, 'post' );
			$id       = call_user_func( $d['create'] );
			$draft_results[ $d['key'] ] = [ 'id' => (int) $id, 'created' => ! $existing ];
		}
	}

	$about_result = '';
	if ( isset( $_POST['stevebaron_about_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_about_nonce'] ) ), 'stevebaron_about' ) ) {
		$force        = ! empty( $_POST['stevebaron_about_force'] );
		$about_result = stevebaron_populate_about_page( $force );
	}

	$now_result = '';
	if ( isset( $_POST['stevebaron_now_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_now_nonce'] ) ), 'stevebaron_now' ) ) {
		$force      = ! empty( $_POST['stevebaron_now_force'] );
		$now_result = stevebaron_populate_now_page( $force );
	}

	$pages = stevebaron_expected_pages();

	// Counts for the CV/Projects status display
	$cv_count       = (int) wp_count_posts( 'sb_experience' )->publish;
	$project_count  = (int) wp_count_posts( 'sb_project' )->publish;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Site Setup', 'stevebaron' ); ?></h1>
		<p>
			<?php esc_html_e( 'Creates the pages this theme expects (Home, About, CV, Projects, Writing, Photos, Now, Contact), binds each to the right page template, sets up Settings → Reading, and builds a Primary nav menu pointed at all of them.', 'stevebaron' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Safe to re-run. It will not touch existing pages other than to set the correct page template if missing.', 'stevebaron' ); ?>
		</p>

		<?php if ( $ran ) : ?>
			<div class="notice notice-success">
				<p><strong><?php esc_html_e( 'Setup complete.', 'stevebaron' ); ?></strong></p>
				<ul style="margin-left:1.5em;list-style:disc;">
					<?php foreach ( $status as $slug => $result ) :
						$label = $pages[ $slug ]['title'] ?? $slug;
						$msg   = [
							'created'          => __( 'Created', 'stevebaron' ),
							'existed'          => __( 'Already existed (left alone)', 'stevebaron' ),
							'template-updated' => __( 'Existed — page template fixed', 'stevebaron' ),
							'error'            => __( 'Error', 'stevebaron' ),
						][ $result ] ?? $result;
					?>
						<li><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $msg ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button"><?php esc_html_e( 'View site →', 'stevebaron' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button"><?php esc_html_e( 'Edit Primary menu', 'stevebaron' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>" class="button"><?php esc_html_e( 'Reading settings', 'stevebaron' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Current state', 'stevebaron' ); ?></h2>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Page', 'stevebaron' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'stevebaron' ); ?></th>
					<th><?php esc_html_e( 'Template', 'stevebaron' ); ?></th>
					<th><?php esc_html_e( 'Status', 'stevebaron' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $pages as $slug => $cfg ) :
					$page = get_page_by_path( $slug );
					$tpl  = $page ? get_post_meta( $page->ID, '_wp_page_template', true ) : '';
					$tpl_ok = ! $cfg['template'] || $tpl === $cfg['template'];
				?>
					<tr>
						<td><?php echo esc_html( $cfg['title'] ); ?></td>
						<td><code><?php echo esc_html( $slug ); ?></code></td>
						<td>
							<?php if ( $cfg['template'] ) : ?>
								<code><?php echo esc_html( $cfg['template'] ); ?></code>
								<?php if ( $page && ! $tpl_ok ) : ?>
									<br><small style="color:#b32d2e;">
										<?php
										/* translators: %s: current template filename */
										printf( esc_html__( 'currently: %s', 'stevebaron' ), '<code>' . esc_html( $tpl ?: 'default' ) . '</code>' );
										?>
									</small>
								<?php endif; ?>
							<?php else : ?>
								<em><?php esc_html_e( 'default', 'stevebaron' ); ?></em>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $page ) : ?>
								<span style="color:#1a7f37;">✓ <?php esc_html_e( 'Exists', 'stevebaron' ); ?></span>
								&nbsp;<a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>"><?php esc_html_e( 'edit', 'stevebaron' ); ?></a>
								&middot; <a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank"><?php esc_html_e( 'view', 'stevebaron' ); ?></a>
							<?php else : ?>
								<span style="color:#b32d2e;">✗ <?php esc_html_e( 'Missing', 'stevebaron' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" style="margin-top:24px;">
			<?php wp_nonce_field( 'stevebaron_setup', 'stevebaron_setup_nonce' ); ?>
			<?php submit_button( __( 'Run site setup', 'stevebaron' ), 'primary large' ); ?>
		</form>

		<hr style="margin:48px 0 24px;">

		<h2><?php esc_html_e( 'CV & Projects content', 'stevebaron' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: 1: CV entry count, 2: project count */
				esc_html__( 'Currently: %1$d CV entries, %2$d projects.', 'stevebaron' ),
				(int) $cv_count,
				(int) $project_count
			);
			?>
		</p>

		<?php if ( $reseeded && $reseed_result ) : ?>
			<div class="notice notice-success">
				<p>
					<strong><?php esc_html_e( 'Reset complete.', 'stevebaron' ); ?></strong>
					<?php
					printf(
						/* translators: 1: CV trashed count, 2: project trashed count, 3: CV inserted count, 4: project inserted count */
						esc_html__( 'Trashed %1$d CV entries and %2$d projects, then inserted %3$d CV entries and %4$d projects from the resume.', 'stevebaron' ),
						(int) $reseed_result['trashed']['cv'],
						(int) $reseed_result['trashed']['projects'],
						(int) $reseed_result['inserted']['cv'],
						(int) $reseed_result['inserted']['projects']
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( home_url( '/cv/' ) ); ?>" class="button" target="_blank"><?php esc_html_e( 'View CV page →', 'stevebaron' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sb_experience&post_status=trash' ) ); ?>" class="button"><?php esc_html_e( 'View trashed entries', 'stevebaron' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<div class="notice notice-warning inline" style="padding:12px 14px;">
			<p style="margin:0;">
				<strong><?php esc_html_e( 'Heads up:', 'stevebaron' ); ?></strong>
				<?php esc_html_e( 'This will send all existing CV Entries and Projects to the Trash (recoverable from the admin) and recreate them from the canonical resume data shipped with the theme. Use this if the seed ran with old placeholder data.', 'stevebaron' ); ?>
			</p>
		</div>

		<form method="post" style="margin-top:16px;" onsubmit="return confirm('<?php echo esc_js( __( "Trash all existing CV Entries and Projects, then reseed from the resume? You can restore them from the admin Trash if needed.", "stevebaron" ) ); ?>');">
			<?php wp_nonce_field( 'stevebaron_reseed', 'stevebaron_reseed_nonce' ); ?>
			<button type="submit" class="button button-secondary" style="color:#b32d2e;border-color:#b32d2e;">
				<?php esc_html_e( 'Reset CV & Projects to resume data', 'stevebaron' ); ?>
			</button>
		</form>

		<hr style="margin:48px 0 24px;">

		<h2><?php esc_html_e( 'Drafts ready to paste', 'stevebaron' ); ?></h2>
		<p>
			<?php esc_html_e( 'Long-form essays shipped with the theme. Click any button to insert it as a draft post — nothing is published. Review, edit, and publish in your own time.', 'stevebaron' ); ?>
		</p>

		<?php foreach ( $drafts as $d ) :
			$result   = $draft_results[ $d['key'] ] ?? null;
			$existing = get_page_by_path( $d['slug'], OBJECT, 'post' );
		?>
			<div class="sb-draft-row" style="padding:14px 0;border-bottom:1px solid #eee;">
				<div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">
					<div style="flex:1;min-width:240px;">
						<div style="font-size:14px;color:#1a1614;"><strong><?php echo esc_html( $d['title'] ); ?></strong></div>
						<div style="font-size:12.5px;color:#4a4138;margin-top:2px;"><?php echo esc_html( $d['desc'] ); ?></div>
					</div>
					<form method="post" style="margin:0;">
						<?php wp_nonce_field( $d['nonce'], $d['nonce'] . '_nonce' ); ?>
						<button type="submit" class="button <?php echo $existing ? '' : 'button-primary'; ?>">
							<?php echo $existing
								? esc_html__( 'Open existing draft', 'stevebaron' )
								: esc_html__( 'Create draft', 'stevebaron' ); ?>
						</button>
					</form>
				</div>
				<?php if ( $result && $result['id'] ) : ?>
					<div class="notice notice-success" style="margin:10px 0 0;">
						<p style="margin:6px 0;">
							<strong>
								<?php echo $result['created']
									? esc_html__( 'Draft created.', 'stevebaron' )
									: esc_html__( 'Draft already existed.', 'stevebaron' ); ?>
							</strong>
							<a href="<?php echo esc_url( get_edit_post_link( $result['id'] ) ); ?>"><?php esc_html_e( 'Open in editor →', 'stevebaron' ); ?></a>
							·
							<a href="<?php echo esc_url( get_preview_post_link( $result['id'] ) ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'stevebaron' ); ?></a>
						</p>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<hr style="margin:48px 0 24px;">

		<h2><?php esc_html_e( 'About page content', 'stevebaron' ); ?></h2>
		<p>
			<?php esc_html_e( 'A long-form About page is shipped with the theme (see content/about-DRAFT.md). Click below to populate your About page with this content. By default it will only write if the page is currently empty — toggle the checkbox to overwrite existing content.', 'stevebaron' ); ?>
		</p>

		<?php if ( $about_result === 'updated' ) : ?>
			<div class="notice notice-success">
				<p><strong><?php esc_html_e( 'About page updated.', 'stevebaron' ); ?></strong></p>
				<p>
					<?php $about_page = get_page_by_path( 'about' ); if ( $about_page ) : ?>
						<a href="<?php echo esc_url( get_edit_post_link( $about_page->ID ) ); ?>" class="button button-primary"><?php esc_html_e( 'Edit in Gutenberg →', 'stevebaron' ); ?></a>
						<a href="<?php echo esc_url( get_permalink( $about_page->ID ) ); ?>" class="button" target="_blank"><?php esc_html_e( 'View About page', 'stevebaron' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
		<?php elseif ( $about_result === 'has-content' ) : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Skipped.', 'stevebaron' ); ?></strong>
					<?php esc_html_e( 'The About page already has content. Check the box below and re-submit to overwrite.', 'stevebaron' ); ?>
				</p>
			</div>
		<?php elseif ( $about_result === 'no-page' ) : ?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'No About page found. Run "Run site setup" above first to create it.', 'stevebaron' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:16px;">
			<?php wp_nonce_field( 'stevebaron_about', 'stevebaron_about_nonce' ); ?>
			<p style="margin:0 0 12px;">
				<label>
					<input type="checkbox" name="stevebaron_about_force" value="1">
					<?php esc_html_e( 'Overwrite existing content (use carefully)', 'stevebaron' ); ?>
				</label>
			</p>
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Populate About page content', 'stevebaron' ); ?>
			</button>
		</form>

		<hr style="margin:48px 0 24px;">

		<h2><?php esc_html_e( 'Now page content', 'stevebaron' ); ?></h2>
		<p>
			<?php esc_html_e( 'Fills in the seven /now/ snapshot items (Working on · Reading · Watching · Learning · Outside · Saying yes to · Saying no to), the location, and the "last updated" stamp. The body of the page gets a short preamble too. Source-of-truth is content/now-DRAFT.md.', 'stevebaron' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'By default, the populator only writes a field if it\'s currently empty — so it\'s safe to re-run after you\'ve customized individual items. Tick the checkbox to overwrite everything.', 'stevebaron' ); ?>
		</p>

		<?php if ( $now_result === 'updated' ) : ?>
			<div class="notice notice-success">
				<p><strong><?php esc_html_e( 'Now page updated.', 'stevebaron' ); ?></strong></p>
				<p>
					<?php $now_page = get_page_by_path( 'now' ); if ( $now_page ) : ?>
						<a href="<?php echo esc_url( get_edit_post_link( $now_page->ID ) ); ?>" class="button button-primary"><?php esc_html_e( 'Edit in admin →', 'stevebaron' ); ?></a>
						<a href="<?php echo esc_url( get_permalink( $now_page->ID ) ); ?>" class="button" target="_blank"><?php esc_html_e( 'View Now page', 'stevebaron' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
		<?php elseif ( $now_result === 'all-filled' ) : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Nothing to write.', 'stevebaron' ); ?></strong>
					<?php esc_html_e( 'All Now-page fields already have content. Tick the override box and re-submit to overwrite them.', 'stevebaron' ); ?>
				</p>
			</div>
		<?php elseif ( $now_result === 'no-page' ) : ?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'No Now page found. Run "Run site setup" above first to create it.', 'stevebaron' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:16px;">
			<?php wp_nonce_field( 'stevebaron_now', 'stevebaron_now_nonce' ); ?>
			<p style="margin:0 0 12px;">
				<label>
					<input type="checkbox" name="stevebaron_now_force" value="1">
					<?php esc_html_e( 'Overwrite existing field values (use carefully)', 'stevebaron' ); ?>
				</label>
			</p>
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Populate Now page content', 'stevebaron' ); ?>
			</button>
		</form>
	</div>
	<?php
}

// ── Dashboard widget ─────────────────────────────────────────────────────

add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget(
		'stevebaron_dashboard',
		__( 'Steve Baron · at a glance', 'stevebaron' ),
		'stevebaron_dashboard_widget'
	);
} );

function stevebaron_dashboard_widget() {
	$posts    = (int) wp_count_posts( 'post' )->publish;
	$drafts   = (int) wp_count_posts( 'post' )->draft;
	$cv       = (int) wp_count_posts( 'sb_experience' )->publish;
	$projects = (int) wp_count_posts( 'sb_project' )->publish;
	$photos   = (int) wp_count_posts( 'sb_photo' )->publish;

	$latest = get_posts( [ 'numberposts' => 3, 'post_status' => [ 'publish', 'draft' ] ] );
	?>
	<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;margin-bottom:16px;">
		<?php foreach ( [
			[ __( 'Posts', 'stevebaron' ),    $posts,    'edit.php' ],
			[ __( 'Drafts', 'stevebaron' ),   $drafts,   'edit.php?post_status=draft' ],
			[ __( 'CV entries', 'stevebaron' ), $cv,     'edit.php?post_type=sb_experience' ],
			[ __( 'Projects', 'stevebaron' ), $projects, 'edit.php?post_type=sb_project' ],
			[ __( 'Photos', 'stevebaron' ),   $photos,   'edit.php?post_type=sb_photo' ],
		] as $card ) : ?>
			<a href="<?php echo esc_url( admin_url( $card[2] ) ); ?>" style="display:block;background:#f4f0e8;border-radius:8px;padding:14px 16px;color:#1a1614;text-decoration:none;border:1px solid rgba(0,0,0,.05);">
				<div style="font-size:28px;font-weight:700;line-height:1.1;color:#c2410c;"><?php echo (int) $card[1]; ?></div>
				<div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#4a4138;margin-top:2px;"><?php echo esc_html( $card[0] ); ?></div>
			</a>
		<?php endforeach; ?>
	</div>

	<?php if ( $latest ) : ?>
		<h3 style="margin-top:8px;font-size:13px;color:#4a4138;"><?php esc_html_e( 'Latest posts', 'stevebaron' ); ?></h3>
		<ul style="margin:0;padding:0;list-style:none;">
			<?php foreach ( $latest as $p ) :
				$status_label = $p->post_status === 'publish' ? '' : ' (' . esc_html( $p->post_status ) . ')';
			?>
				<li style="padding:6px 0;border-bottom:1px solid #eee;">
					<a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" style="text-decoration:none;color:#1a1614;font-weight:600;">
						<?php echo esc_html( get_the_title( $p ) ); ?>
					</a><?php echo $status_label; ?>
					<span style="color:#8a7f6e;font-size:12px;margin-left:8px;"><?php echo esc_html( get_the_date( '', $p ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<p style="margin-top:14px;font-size:12px;">
		<a href="<?php echo esc_url( admin_url( 'tools.php?page=stevebaron-setup' ) ); ?>"><?php esc_html_e( 'Site Setup →', 'stevebaron' ); ?></a>
		&nbsp;·&nbsp;
		<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize →', 'stevebaron' ); ?></a>
		&nbsp;·&nbsp;
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View site →', 'stevebaron' ); ?></a>
	</p>
	<?php
}

// ── Admin notice prompting setup ─────────────────────────────────────────

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( get_option( 'stevebaron_site_setup' ) ) return;
	$screen = get_current_screen();
	if ( $screen && $screen->id === 'tools_page_stevebaron-setup' ) return;
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Steve Baron theme:', 'stevebaron' ); ?></strong>
			<?php esc_html_e( 'Finish setting up your site in one click.', 'stevebaron' ); ?>
			<a href="<?php echo esc_url( admin_url( 'tools.php?page=stevebaron-setup' ) ); ?>" class="button button-primary" style="margin-left:8px;">
				<?php esc_html_e( 'Run Site Setup', 'stevebaron' ); ?>
			</a>
		</p>
	</div>
	<?php
} );
