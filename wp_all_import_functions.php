<?php

add_action('pmxi_saved_post','post_saved',5,2);
function post_saved( $postid, $xml_node) {
    $attachments = get_attached_media( '', $postid );
    $content_post = get_post( $postid );
    $content = $content_post->post_content;
    $content = apply_filters( 'the_content', $content );
    preg_match_all( '@href="([^"]+\.pdf|PDF)"@' , $content, $match_pdf );
    $pdf_files = array_pop( $match_pdf );

    // Fix PDF file url in Post content.
    foreach ( $pdf_files as $pdf_file ) {
        $basename = basename( $pdf_file );
        $basename = str_replace(' ', '-', $basename);
        $basename = str_replace('%20', '-', $basename);
        foreach ( $attachments as $attachment ) {
            if ( preg_match( '/'.$basename.'$/i', $attachment->guid ) ) {
                $content = str_replace( $pdf_file, $attachment->guid, $content );
            }
        }
    }

    // Fix featured image, subtitle and descriptive paragraph.
    $record = json_decode( json_encode( ( array ) $xml_node ), 1 );

    if (!empty($record['PostType']) && $record['PostType'] === "report" & !empty($record['desktop_hero']) && !empty($record['ImageID']) && !empty($record['ImageURL'])) {
        // For report post type use desktop here image as a feature image.
        $record['image_video'] = $record['desktop_hero'];
    }
	
    // Featured/Hero image
    if (!empty($record['image_video']) && !empty($record['ImageID']) && !empty($record['ImageURL'])) {
        $featured_image = '';
        $image_caption = '';
        $image_title = '';

        $image_IDs = explode("|", $record['ImageID']);
        $image_URLs = explode("|", $record['ImageURL']);
        $image_titles = !empty($record['ImageTitle']) ? explode("|", $record['ImageTitle']) : [];
        $image_captions = !empty($record['ImageCaption']) ? explode("|", $record['ImageCaption']) : [];


        // filter featured image from the images array.
        foreach($image_IDs as $index => $image_id) {
            if ($image_id === $record['image_video']) {
                $featured_image = $image_URLs[$index];
                $image_caption = $image_captions[$index];
                $image_title = $image_titles[$index];
                break;
            }
        }
        if ($featured_image) {
            $basename = basename( $featured_image );
            $basename = str_replace(' ', '-', $basename);
            $basename = str_replace('%20', '-', $basename);
            $images = get_attached_media('image', $postid);
            foreach ( $images as $img ) {
                if ( preg_match( '/'.$basename.'$/i', $img->guid ) ) {
                    $featured_image = $img->guid;
                }
            }
            $content = "<figure class='wp-block-image size-large p4featured_image'><img src='".$featured_image."' alt='" . $image_title . "' /><figcaption class='wp-element-caption'>" . $image_caption . "</figcaption></figure>" . $content;
        }
    }
    // Descriptive paragraph
    if (!empty($record['descriptive_paragraph'])) {
        $content = "<span class='p4descriptive_paragraph'>" . $record['descriptive_paragraph'] . "</span><br />" . $content;
    }
    // Subtitle
    if (!empty($record['subtitle'])) {
        $content = "<span class='p4subtitle'>" . $record['subtitle'] . "</span><br />" . $content;
    }

	if (!empty($record['PostType']) && $record['PostType'] === "report") {
        $content = "</div>" . $content;

        // Report download link
        if (!empty($record['download_link'])) {
            $download_link = $record['download_link'];
            $basename = basename( $record['download_link'] );
            $basename = str_replace(' ', '-', $basename);
            $basename = str_replace('%20', '-', $basename);
            foreach ( $attachments as $attachment ) {
                if ( preg_match( '/'.$basename.'$/i', $attachment->guid ) ) {
                    $download_link = $attachment->guid;
                }
            }


            $content = "<p class='downloadLink'><span>Download: </span> <a target='_blank' href='" .$download_link. "'>PDF <i class='fa fa-arrow-circle-down' aria-hidden='true'></i></a></p>" . $content;
            //<p class="downloadLink"><span>Download: </span> <a target="_blank" href="https://www.greenpeace.org/usa/wp-content/uploads/2024/06/Bankrolling-Bitcoin-Report-2024.pdf">PDF <i class="fa fa-arrow-circle-down" aria-hidden="true"></i></a></p>
        }

        // Report Edition
        if (!empty($record['edition'])) {
            $content = "<p class='version'><span>Edition: </span>" . $record['edition'] . "</p>" . $content;
            //<p class="version"><span>Edition: </span>1</p>
        }

        // Report publish date
        if (!empty($record['publish_date'])) {
            $content = "<p class='publishDate'><span>Published: </span>" . $record['publish_date'] . "</p>" . $content;
            //<p class="publishDate"><span>Published: </span>06-14-2024</p>
        }

        $content = "<div class='report-info'>" . $content;
    }

    $updated_post = array();
    $updated_post['ID'] = $postid;
    $updated_post['post_content'] = $content;
    wp_update_post( $updated_post );
}
