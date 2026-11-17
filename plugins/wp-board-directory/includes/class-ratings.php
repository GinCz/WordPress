<?php
/**
 * Rating, Reviews & Anti-Fraud Vote Engine
 *
 * @package WP_Board_Directory
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VB_Ratings {

    public static function init() {
        add_action( 'wp_ajax_vb_submit_vote', [ __CLASS__, 'ajax_submit_vote' ] );
        add_action( 'wp_ajax_nopriv_vb_submit_vote', [ __CLASS__, 'ajax_must_login' ] );
    }

    /**
     * Unauthenticated user rejection
     */
    public static function ajax_must_login() {
        wp_send_json_error( [
            'message' => 'Оценивать объявления и продавцов могут только зарегистрированные пользователи. Пожалуйста, войдите в свой аккаунт.',
            'code'    => 'auth_required',
        ] );
    }

    /**
     * Handle AJAX Vote / Review submission
     */
    public static function ajax_submit_vote() {
        check_ajax_referer( 'vb_board_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            self::ajax_must_login();
        }

        $user_id = get_current_user_id();
        $target_type = sanitize_key( $_POST['target_type'] ?? 'listing' ); // 'listing' or 'author'
        $target_id   = absint( $_POST['target_id'] ?? 0 );
        $rating      = intval( $_POST['rating'] ?? 0 ); // +1 or -1, or 1 to 5
        $comment     = sanitize_textarea_field( $_POST['comment'] ?? '' );
        $ip_address  = self::get_client_ip();

        if ( ! in_array( $target_type, [ 'listing', 'author' ], true ) || ! $target_id ) {
            wp_send_json_error( [ 'message' => 'Некорректные параметры оценки.' ] );
        }

        // 1. Prevent Self-Rating / Self-Voting
        if ( 'listing' === $target_type ) {
            $listing = get_post( $target_id );
            if ( ! $listing || $listing->post_type !== 'vb_listing' ) {
                wp_send_json_error( [ 'message' => 'Объявление не найдено.' ] );
            }
            if ( (int) $listing->post_author === $user_id ) {
                wp_send_json_error( [ 'message' => 'Вы не можете голосовать за собственное объявление!' ] );
            }
        } elseif ( 'author' === $target_type ) {
            if ( $target_id === $user_id ) {
                wp_send_json_error( [ 'message' => 'Вы не можете ставить оценку самому себе!' ] );
            }
        }

        global $wpdb;
        $table = $wpdb->prefix . 'vb_ratings';

        // 2. Anti-fraud check: 1 vote per user per target
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE target_type = %s AND target_id = %d AND user_id = %d",
            $target_type,
            $target_id,
            $user_id
        ) );

        if ( $existing ) {
            wp_send_json_error( [ 'message' => 'Вы уже оставили свой голос ранее. Повторное голосование запрещено.' ] );
        }

        // 3. Anti-Flood / Rate limit: max 5 votes per hour per IP/user
        $hour_ago = date( 'Y-m-d H:i:s', time() - 3600 );
        $recent_votes = $wpdb->get_var( $wpdb->prepare(
            "SELECT count(*) FROM {$table} WHERE (user_id = %d OR ip_address = %s) AND created_at > %s",
            $user_id,
            $ip_address,
            $hour_ago
        ) );

        if ( $recent_votes >= 10 ) {
            wp_send_json_error( [ 'message' => 'Слишком много оценок за короткое время. Защита от накрутки активна, попробуйте позже.' ] );
        }

        // 4. Insert Vote
        $inserted = $wpdb->insert(
            $table,
            [
                'target_type'    => $target_type,
                'target_id'      => $target_id,
                'user_id'        => $user_id,
                'rating'         => $rating,
                'review_content' => $comment,
                'ip_address'     => $ip_address,
                'status'         => 'approved',
                'created_at'     => current_time( 'mysql' ),
            ],
            [ '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s' ]
        );

        if ( ! $inserted ) {
            wp_send_json_error( [ 'message' => 'Ошибка базы данных при сохранении оценки.' ] );
        }

        // 5. Update Rating Aggregate
        $score = self::recalculate_score( $target_type, $target_id );

        wp_send_json_success( [
            'message' => 'Спасибо! Ваш голос успешно учтен.',
            'score'   => $score,
        ] );
    }

    /**
     * Recalculate and update aggregate score
     */
    public static function recalculate_score( $target_type, $target_id ) {
        global $wpdb;
        $table_ratings = $wpdb->prefix . 'vb_ratings';
        $table_index   = $wpdb->prefix . 'vb_index';

        $total_score = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(rating) FROM {$table_ratings} WHERE target_type = %s AND target_id = %d AND status = 'approved'",
            $target_type,
            $target_id
        ) );

        $total_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_ratings} WHERE target_type = %s AND target_id = %d AND status = 'approved'",
            $target_type,
            $target_id
        ) );

        if ( 'listing' === $target_type ) {
            update_post_meta( $target_id, '_vb_rating_score', $total_score );
            update_post_meta( $target_id, '_vb_rating_count', $total_count );

            $wpdb->update(
                $table_index,
                [ 'rating_score' => $total_score, 'rating_count' => $total_count ],
                [ 'listing_id' => $target_id ],
                [ '%d', '%d' ],
                [ '%d' ]
            );
        } elseif ( 'author' === $target_type ) {
            update_user_meta( $target_id, '_vb_seller_rating', $total_score );
            update_user_meta( $target_id, '_vb_seller_votes', $total_count );
        }

        return [ 'score' => $total_score, 'count' => $total_count ];
    }

    /**
     * Get real client IP behind Cloudflare
     */
    public static function get_client_ip() {
        if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
            return sanitize_text_field( $_SERVER['HTTP_CF_CONNECTING_IP'] );
        }
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $parts = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            return trim( sanitize_text_field( $parts[0] ) );
        }
        return sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' );
    }
}
