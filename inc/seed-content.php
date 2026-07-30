<?php
/**
 * Seeds demo content the theme still owns: 3 testimonials and 6 blog posts.
 *
 * Excursions are no longer seeded here — the Nefertari Booking Core plugin
 * owns that post type and ships its own demo importer (Tools page in the
 * plugin's admin) with matching slots/capacity, which this theme can't create.
 *
 * Each content type skips itself if that type already has published posts,
 * so a re-activation never creates duplicates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_seed_content() {
	nefertari_seed_testimonials();
	nefertari_seed_blog_posts();
}
add_action( 'after_switch_theme', 'nefertari_seed_content' );

function nefertari_seed_testimonials() {
	if ( (int) wp_count_posts( 'testimonial' )->publish > 0 ) {
		return;
	}
	foreach ( nefertari_seed_testimonial_data() as $data ) {
		$post_id = wp_insert_post( array(
			'post_type'    => 'testimonial',
			'post_status'  => 'publish',
			'post_title'   => $data['name'],
			'post_content' => $data['text'],
		) );
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}
		update_post_meta( $post_id, '_nx_meta_line', $data['meta'] );
		update_post_meta( $post_id, '_nx_rating', 5 );
	}
}

function nefertari_seed_get_or_create_category( $name ) {
	$term = term_exists( $name, 'category' );
	if ( $term ) {
		return (int) $term['term_id'];
	}
	$inserted = wp_insert_term( $name, 'category' );
	if ( is_wp_error( $inserted ) ) {
		return (int) get_option( 'default_category' );
	}
	return (int) $inserted['term_id'];
}

function nefertari_seed_blog_posts() {
	if ( (int) wp_count_posts( 'post' )->publish > 0 ) {
		return;
	}
	foreach ( nefertari_seed_blog_post_data() as $data ) {
		$category_id = nefertari_seed_get_or_create_category( $data['category'] );

		$body = '';
		foreach ( $data['body'] as $block ) {
			if ( isset( $block['h'] ) ) {
				$body .= '<h2>' . esc_html( $block['h'] ) . "</h2>\n";
			} elseif ( isset( $block['p'] ) ) {
				$body .= '<p>' . esc_html( $block['p'] ) . "</p>\n";
			}
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $data['title'],
			'post_excerpt' => $data['excerpt'],
			'post_content' => $body,
			'post_category' => array( $category_id ),
			'post_date'    => $data['date'],
		) );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_nx_image_url', $data['img'] );
	}
}

/* -------------------------------------------------------------------------
 * Data — ported 1:1 from the exported design.
 * ---------------------------------------------------------------------- */

function nefertari_seed_testimonial_data() {
	return array(
		array(
			'name' => 'Sarah M.', 'meta' => 'United Kingdom · Cairo & Pyramids',
			'text' => 'Our guide was incredibly knowledgeable and the whole day was seamless — pickup was right on time and the Pyramids left us speechless. Booking on WhatsApp took two minutes.',
		),
		array(
			'name' => 'Lukas R.', 'meta' => 'Germany · Sea Trip & Snorkeling',
			'text' => 'We saw dolphins! The reef snorkeling was unreal and the crew looked after the kids the whole time. Best value excursion of our holiday in Hurghada.',
		),
		array(
			'name' => 'Amira K.', 'meta' => 'UAE · Desert Safari',
			'text' => 'The quad biking and Bedouin dinner under the stars was magical. Everything felt safe and well organised. Highly recommend Nefertari to anyone visiting Egypt.',
		),
	);
}

function nefertari_seed_blog_post_data() {
	return array(
		array(
			'title' => 'The best time to visit Egypt — month by month', 'category' => 'Travel tips', 'date' => '2026-05-04 09:00:00',
			'img' => 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=1000&q=80',
			'excerpt' => 'Cooler temples in winter, quiet reefs in spring — here’s exactly when to come for the trip you have in mind.',
			'body' => array(
				array( 'h' => 'Winter (Nov–Feb): the sweet spot' ),
				array( 'p' => 'This is peak season for good reason. Daytime highs in Luxor and Aswan sit around a comfortable 22–25°C, perfect for long days among the temples and tombs. The Red Sea stays warm enough to snorkel, and evenings call for a light jacket.' ),
				array( 'h' => 'Spring (Mar–May): warm and uncrowded' ),
				array( 'p' => 'Crowds thin out after February while the sea keeps warming. It’s an ideal window for combining desert safaris with diving — just watch for the occasional khamaseen wind in April.' ),
				array( 'h' => 'Summer (Jun–Aug): sea over sand' ),
				array( 'p' => 'Inland sites get genuinely hot, so summer is when the Red Sea shines. Start excursions early, hydrate well, and save the Pyramids and Luxor for sunrise departures.' ),
				array( 'h' => 'Autumn (Sep–Oct): the quiet reward' ),
				array( 'p' => 'Temperatures ease, prices dip, and the water is at its warmest of the year. Many regulars call October the single best month to visit Egypt.' ),
			),
		),
		array(
			'title' => 'A first-timer’s guide to the Pyramids of Giza', 'category' => 'Heritage', 'date' => '2026-04-18 09:00:00',
			'img' => 'https://images.unsplash.com/photo-1503177119275-0aa32b3a9368?w=1000&q=80',
			'excerpt' => 'Tickets, timing and the photo spots locals actually use — everything you need before you stand before the Great Pyramid.',
			'body' => array(
				array( 'h' => 'Go early, go slow' ),
				array( 'p' => 'Gates open at 7am. Arriving in the first hour means cooler air, softer light and a plateau that hasn’t yet filled with coaches. Give yourself three hours minimum.' ),
				array( 'h' => 'Inside or outside?' ),
				array( 'p' => 'A separate ticket lets you climb into the Great Pyramid. The passage is steep, low and unventilated — thrilling for some, claustrophobic for others. The smaller pyramids of Khafre and Menkaure offer a gentler version of the same experience.' ),
				array( 'h' => 'The view most people miss' ),
				array( 'p' => 'Drive 10 minutes to the panorama point southwest of the plateau. From there all three pyramids line up against the desert — the classic shot, and the calmest spot to take it.' ),
				array( 'h' => 'Pair it with the Museum' ),
				array( 'p' => 'The Grand Egyptian Museum sits minutes away and holds Tutankhamun’s complete collection. Seeing the treasures the same day you see the tombs they came from is hard to beat.' ),
			),
		),
		array(
			'title' => 'Snorkeling the Red Sea: reefs, fish and what to expect', 'category' => 'Sea', 'date' => '2026-04-06 09:00:00',
			'img' => 'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?w=1000&q=80',
			'excerpt' => 'You don’t need to dive to meet the Red Sea’s underwater world. Here’s how to make the most of a snorkeling day.',
			'body' => array(
				array( 'h' => 'Some of the richest reefs on Earth' ),
				array( 'p' => 'The Red Sea hosts over 200 species of coral and more than 1,000 species of fish. Around Hurghada, sites like Giftun Island offer shallow coral gardens that are vivid even a metre below the surface.' ),
				array( 'h' => 'What you’ll see' ),
				array( 'p' => 'Expect clouds of orange anthias, butterflyfish, parrotfish grazing the coral, and — if you’re lucky — a passing turtle or a pod of spinner dolphins on the boat ride out.' ),
				array( 'h' => 'Make it comfortable' ),
				array( 'p' => 'Bring reef-safe sunscreen, a rash vest for sun protection, and defog your mask before you jump in. Our boats carry gear in all sizes, but a mask that fits your own face is always best.' ),
			),
		),
		array(
			'title' => 'A night in the desert: what a Bedouin safari is really like', 'category' => 'Desert', 'date' => '2026-03-21 09:00:00',
			'img' => 'https://images.unsplash.com/photo-1547234935-80c7145ec969?w=1000&q=80',
			'excerpt' => 'Quad bikes, sweet tea and a sky full of stars — a look at how an evening in the Eastern Desert unfolds.',
			'body' => array(
				array( 'h' => 'The ride out' ),
				array( 'p' => 'The adventure starts mid-afternoon as the heat softens. Quad bikes and 4x4s carry you over rolling dunes toward a Bedouin camp set against the mountains.' ),
				array( 'h' => 'Tea, bread and tradition' ),
				array( 'p' => 'At camp you’ll watch flatbread baked over open coals and share sweet Bedouin tea. It’s unhurried, warm and genuinely welcoming — not a performance.' ),
				array( 'h' => 'Then the sky arrives' ),
				array( 'p' => 'With no city light for miles, the stars are overwhelming. Many trips include a telescope; even without one, the Milky Way is plainly visible. A barbecue dinner under that sky is the highlight for most travellers.' ),
			),
		),
		array(
			'title' => 'What to pack for a week of Egyptian excursions', 'category' => 'Travel tips', 'date' => '2026-03-09 09:00:00',
			'img' => 'https://images.unsplash.com/photo-1559494007-9f5847c49d94?w=1000&q=80',
			'excerpt' => 'From temple modesty to reef shoes — a practical packing list that covers desert, sea and city in one bag.',
			'body' => array(
				array( 'h' => 'Layers beat heavy clothes' ),
				array( 'p' => 'Light, breathable fabrics keep you cool by day; a thin layer handles cool desert evenings and over-air-conditioned coaches. Pack a scarf — useful for sun, dust and covering shoulders at religious sites.' ),
				array( 'h' => 'For the water' ),
				array( 'p' => 'Swimwear, a quick-dry towel and reef-safe sunscreen are essentials. Reef shoes protect your feet on rocky entries and are well worth the small space they take.' ),
				array( 'h' => 'Small things that matter' ),
				array( 'p' => 'A refillable water bottle, a power bank, a hat and sunglasses, and a few small notes for tips. Comfortable closed shoes make long days on temple stone far easier than sandals.' ),
			),
		),
		array(
			'title' => 'One perfect day in Luxor: how to see the City of Kings', 'category' => 'Heritage', 'date' => '2026-02-14 09:00:00',
			'img' => 'https://images.unsplash.com/photo-1553913861-c0fddf2619ee?w=1000&q=80',
			'excerpt' => 'Three thousand years of history in a single day — a route through Luxor that balances the must-sees with breathing room.',
			'body' => array(
				array( 'h' => 'Start on the West Bank' ),
				array( 'p' => 'Beat the heat and the crowds by crossing the Nile early for the Valley of the Kings. Three tombs are included on a standard ticket — choose ones with the brightest surviving colour and ask your guide which are open that day.' ),
				array( 'h' => 'Hatshepsut and the Colossi' ),
				array( 'p' => 'The terraced temple of Hatshepsut rises straight from the cliffs and looks almost modern. On the way back, pause at the towering Colossi of Memnon — a quick but unmissable stop.' ),
				array( 'h' => 'Finish at Karnak' ),
				array( 'p' => 'Save the largest religious complex ever built for late afternoon, when the light turns the great hall of columns gold. It’s the perfect place to end a day that spans millennia.' ),
			),
		),
	);
}
