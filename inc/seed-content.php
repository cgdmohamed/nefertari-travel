<?php
/**
 * Seeds demo content on theme activation so the site matches the original
 * design mockup out of the box: 8 excursions, 6 blog posts, 3 testimonials.
 *
 * Each content type is seeded independently and skips itself if that type
 * already has published posts — so a re-activation (including retrying
 * after a partial failure) never creates duplicates. Safe to delete this
 * file entirely once real content — or the planned data plugin — takes over.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nefertari_seed_content() {
	nefertari_seed_excursions();
	nefertari_seed_testimonials();
	nefertari_seed_blog_posts();
}
add_action( 'after_switch_theme', 'nefertari_seed_content' );

function nefertari_seed_set_term( $post_id, $term_name ) {
	wp_set_object_terms( $post_id, $term_name, 'excursion_category' );
}

function nefertari_seed_excursions() {
	if ( (int) wp_count_posts( 'excursion' )->publish > 0 ) {
		return;
	}
	foreach ( nefertari_seed_excursion_data() as $data ) {
		$post_id = wp_insert_post( array(
			'post_type'    => 'excursion',
			'post_status'  => 'publish',
			'post_title'   => $data['name'],
			'post_content' => $data['long'],
			'post_excerpt' => $data['blurb'],
		) );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		nefertari_seed_set_term( $post_id, $data['category'] );

		update_post_meta( $post_id, '_nx_location', $data['location'] );
		update_post_meta( $post_id, '_nx_duration', $data['duration'] );
		update_post_meta( $post_id, '_nx_price', $data['price'] );
		update_post_meta( $post_id, '_nx_rating', $data['rating'] );
		update_post_meta( $post_id, '_nx_reviews_count', $data['reviews_count'] );
		update_post_meta( $post_id, '_nx_booked_count', $data['booked_count'] );
		update_post_meta( $post_id, '_nx_gradient_start', $data['gradient'][0] );
		update_post_meta( $post_id, '_nx_gradient_end', $data['gradient'][1] );
		update_post_meta( $post_id, '_nx_image_url', $data['img'] );
		update_post_meta( $post_id, '_nx_gallery', $data['gallery'] );
		update_post_meta( $post_id, '_nx_highlights', $data['highlights'] );
		update_post_meta( $post_id, '_nx_includes', $data['includes'] );
		update_post_meta( $post_id, '_nx_excludes', $data['excludes'] );
		update_post_meta( $post_id, '_nx_itinerary', $data['itinerary'] );
	}
}

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

function nefertari_seed_excursion_data() {
	return array(
		array(
			'name' => 'Desert Safari Adventure', 'category' => 'Desert', 'location' => 'Hurghada Desert',
			'img' => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1547234935-80c7145ec969?w=600&q=80',
				'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=600&q=80',
				'https://images.unsplash.com/photo-1553913861-c0fddf2619ee?w=600&q=80',
			),
			'duration' => '6 hours', 'price' => 35, 'gradient' => array( '#FBBA6A', '#F5786D' ),
			'blurb' => 'Quad bikes across golden dunes, camel rides and a Bedouin dinner under the stars.',
			'long' => 'Race across the open desert on a quad bike, meet a Bedouin community, and watch the sun melt into the dunes before a traditional BBQ dinner and star-gazing. A high-energy evening that ends in total desert calm.',
			'highlights' => array( 'Quad bike & buggy ride', 'Camel trek through the dunes', 'Bedouin village visit', 'Sunset & star-gazing dinner' ),
			'includes' => array( 'Hotel transfers', 'Quad bike rental', 'BBQ dinner & drinks', 'English-speaking guide' ),
			'excludes' => array( 'Personal expenses & tips', 'Optional camel ride upgrade', 'Alcoholic drinks' ),
			'rating' => '4.9', 'reviews_count' => 842, 'booked_count' => 58,
			'itinerary' => array(
				array( 'label' => 'Pickup & quad biking', 'steps' => array(
					array( 'time' => '2:30 PM', 'title' => 'Hotel pickup', 'text' => 'Air-conditioned transfer from your hotel out to the Eastern Desert base camp.' ),
					array( 'time' => '3:30 PM', 'title' => 'Quad bike & buggy ride', 'text' => 'Race across the open dunes on your own quad bike with a lead guide.' ),
					array( 'time' => '5:00 PM', 'title' => 'Camel trek & Bedouin village', 'text' => 'Swap engines for a camel and visit a Bedouin community for sweet tea.' ),
					array( 'time' => '6:30 PM', 'title' => 'Sunset, BBQ dinner & stars', 'text' => 'Watch the sun set over the dunes, then a traditional BBQ dinner and star-gazing before your transfer back.' ),
				) ),
			),
		),
		array(
			'name' => 'Cairo & The Pyramids', 'category' => 'Heritage', 'location' => 'Giza & Cairo',
			'img' => 'https://images.unsplash.com/photo-1539768942893-daf53e448371?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1568322445389-f64ac2515020?w=600&q=80',
				'https://images.unsplash.com/photo-1503177119275-0aa32b3a9368?w=600&q=80',
				'https://images.unsplash.com/photo-1553913861-c0fddf2619ee?w=600&q=80',
			),
			'duration' => 'Full day', 'price' => 95, 'gradient' => array( '#F5786D', '#D93A7C' ),
			'blurb' => 'The Great Pyramids, the Sphinx and the treasures of the Egyptian Museum in one epic day.',
			'long' => 'Stand before the last surviving wonder of the ancient world, gaze up at the Sphinx, then walk among the golden treasures of Tutankhamun at the Egyptian Museum. We finish in the bustling lanes of Khan el-Khalili bazaar.',
			'highlights' => array( 'Pyramids of Giza & the Sphinx', 'Egyptian Museum & Tutankhamun', 'Khan el-Khalili bazaar', 'Optional camel ride' ),
			'includes' => array( 'A/C transfer or flight', 'All entry tickets', 'Egyptologist guide', 'Lunch' ),
			'excludes' => array( 'Egyptian visa fees', 'Optional camel ride at the Pyramids', 'Tips & personal expenses', 'Drinks at lunch' ),
			'rating' => '4.9', 'reviews_count' => 1290, 'booked_count' => 74,
			'itinerary' => array(
				array( 'label' => 'Giza Plateau & Sphinx', 'steps' => array(
					array( 'time' => '5:00 AM', 'title' => 'Early transfer to Cairo', 'text' => 'Pickup and air-conditioned drive (or short flight) to Giza.' ),
					array( 'time' => '9:00 AM', 'title' => 'Pyramids of Giza', 'text' => 'Stand before the Great Pyramid and the Sphinx with your Egyptologist.' ),
					array( 'time' => '12:30 PM', 'title' => 'Lunch', 'text' => 'Local lunch with a view of the plateau.' ),
				) ),
				array( 'label' => 'Museum & old Cairo', 'steps' => array(
					array( 'time' => '2:00 PM', 'title' => 'The Egyptian Museum', 'text' => 'Walk among the treasures of Tutankhamun and royal mummies.' ),
					array( 'time' => '4:30 PM', 'title' => 'Khan el-Khalili bazaar', 'text' => 'Wander the historic lanes for spices, lanterns and souvenirs.' ),
					array( 'time' => '6:00 PM', 'title' => 'Return transfer', 'text' => 'Relax on the journey back to your resort.' ),
				) ),
			),
		),
		array(
			'name' => 'Luxor — City of Kings', 'category' => 'Heritage', 'location' => 'Luxor',
			'img' => 'https://images.unsplash.com/photo-1568322445389-f64ac2515020?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1568322445389-f64ac2515020?w=600&q=80',
				'https://images.unsplash.com/photo-1503177119275-0aa32b3a9368?w=600&q=80',
				'https://images.unsplash.com/photo-1553913861-c0fddf2619ee?w=600&q=80',
			),
			'duration' => 'Full day', 'price' => 110, 'gradient' => array( '#D93A7C', '#FBBA6A' ),
			'blurb' => 'Cross to the Valley of the Kings and stand before the mighty temples of Karnak.',
			'long' => "Journey to the world's greatest open-air museum. Descend into the painted tombs of the Valley of the Kings, marvel at Hatshepsut's terraced temple, and walk the colossal columns of Karnak — over 3,000 years of history in a single day.",
			'highlights' => array( 'Valley of the Kings', 'Karnak & Luxor Temples', 'Hatshepsut Temple', 'Colossi of Memnon' ),
			'includes' => array( 'Transfers', 'All entry fees', 'Expert guide', 'Lunch' ),
			'excludes' => array( 'Hot-air balloon ride (optional)', 'Tips for guide & driver', 'Drinks & personal expenses' ),
			'rating' => '4.8', 'reviews_count' => 760, 'booked_count' => 46,
			'itinerary' => array(
				array( 'label' => 'West Bank — Valley of the Kings', 'steps' => array(
					array( 'time' => '5:00 AM', 'title' => 'Transfer to Luxor', 'text' => 'Comfortable drive to the City of Kings as the desert wakes.' ),
					array( 'time' => '9:00 AM', 'title' => 'Valley of the Kings', 'text' => 'Descend into the painted tombs of the pharaohs.' ),
					array( 'time' => '11:00 AM', 'title' => 'Hatshepsut & the Colossi', 'text' => 'The terraced temple and the towering Colossi of Memnon.' ),
				) ),
				array( 'label' => 'East Bank — the great temples', 'steps' => array(
					array( 'time' => '1:00 PM', 'title' => 'Lunch by the Nile', 'text' => 'Riverside lunch before the afternoon temples.' ),
					array( 'time' => '3:00 PM', 'title' => 'Karnak & Luxor Temples', 'text' => 'Walk the colossal columns of Karnak in golden afternoon light.' ),
					array( 'time' => '5:30 PM', 'title' => 'Return transfer', 'text' => 'Journey back with thousands of years behind you.' ),
				) ),
			),
		),
		array(
			'name' => 'Aswan & Nubian Wonders', 'category' => 'Nile', 'location' => 'Aswan',
			'img' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=600&q=80',
				'https://images.unsplash.com/photo-1553913861-c0fddf2619ee?w=600&q=80',
				'https://images.unsplash.com/photo-1559494007-9f5847c49d94?w=600&q=80',
			),
			'duration' => 'Full day', 'price' => 120, 'gradient' => array( '#F5786D', '#FBBA6A' ),
			'blurb' => 'Sail to Philae Temple, marvel at the High Dam and ride a felucca through Nubian villages.',
			'long' => 'Glide across the Nile to the island temple of Philae, take in the engineering of the Aswan High Dam, and drift past colourful Nubian villages aboard a traditional felucca as the river breeze cools the afternoon.',
			'highlights' => array( 'Philae Temple by boat', 'Aswan High Dam', 'Unfinished Obelisk', 'Felucca on the Nile' ),
			'includes' => array( 'Transfers', 'All entry fees', 'Felucca ride', 'Guide & lunch' ),
			'excludes' => array( 'Optional Abu Simbel extension', 'Tips & personal expenses', 'Drinks at lunch' ),
			'rating' => '4.9', 'reviews_count' => 540, 'booked_count' => 33,
			'itinerary' => array(
				array( 'label' => 'Dam, obelisk & Philae', 'steps' => array(
					array( 'time' => '6:00 AM', 'title' => 'Transfer to Aswan', 'text' => 'Scenic drive south along the Nile.' ),
					array( 'time' => '9:30 AM', 'title' => 'High Dam & Unfinished Obelisk', 'text' => 'Marvel at modern and ancient engineering.' ),
					array( 'time' => '11:00 AM', 'title' => 'Philae Temple by boat', 'text' => 'Sail to the island temple of Isis.' ),
				) ),
				array( 'label' => 'Felucca & Nubian villages', 'steps' => array(
					array( 'time' => '1:30 PM', 'title' => 'Lunch', 'text' => 'Traditional Nubian-style lunch.' ),
					array( 'time' => '3:00 PM', 'title' => 'Felucca on the Nile', 'text' => 'Drift past colourful Nubian villages on a traditional sailboat.' ),
					array( 'time' => '5:00 PM', 'title' => 'Return transfer', 'text' => 'Head home as the river breeze cools the afternoon.' ),
				) ),
			),
		),
		array(
			'name' => 'Sea Trip & Snorkeling', 'category' => 'Sea', 'location' => 'Red Sea',
			'img' => 'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?w=600&q=80',
				'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600&q=80',
				'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=600&q=80',
			),
			'duration' => 'Full day', 'price' => 30, 'gradient' => array( '#36A9E1', '#1AA7A0' ),
			'blurb' => 'Cruise to Giftun Island, snorkel vivid reefs and chase dolphins across turquoise water.',
			'long' => 'Set sail across the Red Sea to the white sands of Giftun Island. Snorkel above coral gardens bursting with fish, keep watch for playful dolphins, and relax on deck with a fresh buffet lunch between swims.',
			'highlights' => array( 'Dolphin watching', 'Snorkeling at coral reefs', 'Giftun Island beach time', 'Onboard buffet lunch' ),
			'includes' => array( 'Boat trip', 'Snorkeling gear', 'Buffet lunch & drinks', 'Hotel transfers' ),
			'excludes' => array( 'Optional diving upgrade', 'Tips for the crew', 'Alcoholic drinks' ),
			'rating' => '4.9', 'reviews_count' => 2100, 'booked_count' => 118,
			'itinerary' => array(
				array( 'label' => 'Out to sea & snorkeling', 'steps' => array(
					array( 'time' => '8:00 AM', 'title' => 'Hotel pickup', 'text' => 'Transfer to the marina and board your boat.' ),
					array( 'time' => '9:30 AM', 'title' => 'Dolphin watching', 'text' => 'Cruise turquoise water on the lookout for playful dolphins.' ),
					array( 'time' => '11:00 AM', 'title' => 'Snorkeling at the reefs', 'text' => 'Swim over vivid coral gardens bursting with fish.' ),
					array( 'time' => '1:00 PM', 'title' => 'Giftun Island & buffet lunch', 'text' => 'Beach time on white sand and a fresh buffet aboard.' ),
					array( 'time' => '3:30 PM', 'title' => 'Return to marina', 'text' => 'Sail back and transfer to your hotel.' ),
				) ),
			),
		),
		array(
			'name' => 'Diving Trip', 'category' => 'Sea', 'location' => 'Red Sea',
			'img' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600&q=80',
				'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?w=600&q=80',
				'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=600&q=80',
			),
			'duration' => 'Full day', 'price' => 55, 'gradient' => array( '#1AA7A0', '#127E9A' ),
			'blurb' => 'Two guided dives over living coral gardens — beginners and certified divers welcome.',
			'long' => "Descend into one of the world's richest reef systems. First-timers get a full intro session with a PADI instructor, while certified divers explore deeper walls and drop-offs alive with turtles, rays and shoals of tropical fish.",
			'highlights' => array( 'Two guided dives', 'PADI-certified instructors', 'Intro session for beginners', 'Reef walls & marine life' ),
			'includes' => array( 'Full diving gear', 'Certified instructor', '2 dives', 'Lunch & transfers' ),
			'excludes' => array( 'Underwater camera hire', 'Tips for instructor', 'Dive certification course' ),
			'rating' => '4.8', 'reviews_count' => 680, 'booked_count' => 51,
			'itinerary' => array(
				array( 'label' => 'Two guided dives', 'steps' => array(
					array( 'time' => '8:00 AM', 'title' => 'Hotel pickup & briefing', 'text' => 'Transfer to the dive boat and meet your PADI instructor.' ),
					array( 'time' => '10:00 AM', 'title' => 'First dive', 'text' => 'Intro session for beginners or a guided reef dive for the certified.' ),
					array( 'time' => '12:30 PM', 'title' => 'Lunch & surface interval', 'text' => 'Hot lunch aboard between dives.' ),
					array( 'time' => '2:00 PM', 'title' => 'Second dive', 'text' => 'Explore deeper walls alive with turtles, rays and tropical fish.' ),
					array( 'time' => '4:00 PM', 'title' => 'Return transfer', 'text' => 'Back to the marina and your hotel.' ),
				) ),
			),
		),
		array(
			'name' => 'Aqua Park Day', 'category' => 'Family', 'location' => 'Hurghada',
			'img' => 'https://images.unsplash.com/photo-1530549387789-4c1017266635?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1530549387789-4c1017266635?w=600&q=80',
				'https://images.unsplash.com/photo-1551918120-9739cb430c6d?w=600&q=80',
				'https://images.unsplash.com/photo-1559494007-9f5847c49d94?w=600&q=80',
			),
			'duration' => 'Full day', 'price' => 25, 'gradient' => array( '#36A9E1', '#5BC0EB' ),
			'blurb' => 'Slides, lazy rivers and splash zones — a full day of fun for the whole family.',
			'long' => 'A splash-packed day out for all ages. Race down dozens of slides, float the lazy river, and let the kids loose in the supervised splash zone while you unwind on a sun lounger between dips.',
			'highlights' => array( '30+ water slides', 'Kids splash zone', 'Lazy river & pools', 'Lounge & snack areas' ),
			'includes' => array( 'Park entry', 'Hotel transfers', 'Sun loungers', 'Lifeguard supervision' ),
			'excludes' => array( 'Food & drinks not in the package', 'Locker & towel hire', 'Personal expenses' ),
			'rating' => '4.9', 'reviews_count' => 1450, 'booked_count' => 96,
			'itinerary' => array(
				array( 'label' => 'A full day of splashes', 'steps' => array(
					array( 'time' => '9:30 AM', 'title' => 'Hotel pickup', 'text' => 'Transfer to the aqua park and grab your loungers.' ),
					array( 'time' => '10:30 AM', 'title' => 'Slides & lazy river', 'text' => '30+ slides and a drifting lazy river for all ages.' ),
					array( 'time' => '1:00 PM', 'title' => 'Lunch break', 'text' => 'Snacks and lunch in the shaded areas.' ),
					array( 'time' => '4:30 PM', 'title' => 'Return transfer', 'text' => 'Dry off and head back to your hotel.' ),
				) ),
			),
		),
		array(
			'name' => 'Glass Boat Tour', 'category' => 'Sea', 'location' => 'Red Sea',
			'img' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1000&q=80',
			'gallery' => array(
				'https://images.unsplash.com/photo-1437622368342-7a3d73a34c8f?w=600&q=80',
				'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=600&q=80',
				'https://images.unsplash.com/photo-1551918120-9739cb430c6d?w=600&q=80',
			),
			'duration' => '2 hours', 'price' => 20, 'gradient' => array( '#1AA7A0', '#36A9E1' ),
			'blurb' => "See the Red Sea's coral world and rainbow fish from a glass-bottom boat — no swimming needed.",
			'long' => "Stay perfectly dry while the Red Sea's underwater garden glides beneath your feet. Through the glass hull you'll spot coral formations, rays and clouds of tropical fish — the easiest way to meet the reef, perfect for kids and non-swimmers.",
			'highlights' => array( 'Glass-bottom viewing', 'Coral reef gardens', 'Tropical fish & rays', 'Great for all ages' ),
			'includes' => array( 'Boat tour', 'Hotel transfers', 'Soft drinks', 'Onboard guide' ),
			'excludes' => array( 'Tips for the crew', 'Snacks & extra drinks', 'Personal expenses' ),
			'rating' => '4.9', 'reviews_count' => 990, 'booked_count' => 67,
			'itinerary' => array(
				array( 'label' => 'Reef viewing without getting wet', 'steps' => array(
					array( 'time' => '10:00 AM', 'title' => 'Hotel pickup', 'text' => 'Short transfer to the marina.' ),
					array( 'time' => '10:30 AM', 'title' => 'Glass-bottom cruise', 'text' => 'Watch coral gardens, rays and rainbow fish glide beneath the hull.' ),
					array( 'time' => '12:00 PM', 'title' => 'Return transfer', 'text' => 'Back to your hotel — perfect for kids and non-swimmers.' ),
				) ),
			),
		),
	);
}

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
