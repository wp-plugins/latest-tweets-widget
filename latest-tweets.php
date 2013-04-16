<?php
/*
Plugin Name: Latest Tweets
Plugin URI: http://wordpress.org/extend/plugins/latest-tweets-widget/
Description: Provides a sidebar widget showing latest tweets - compatible with the new Twitter API 1.1
Author: Tim Whitlock
Version: 1.0.6
Author URI: http://timwhitlock.info/
*/



/**
 * Pull latest tweets with some caching of raw data.
 * @param string account whose tweets we're pulling
 * @param int number of tweets to get and display
 * @param bool whether to show retweets
 * @param bool whether to show at replies
 * @return array blocks of html expected by the widget
 */
function latest_tweets_render( $screen_name, $count, $rts, $ats ){
    try {
        if( ! function_exists('twitter_api_get') ){
            require_once dirname(__FILE__).'/lib/twitter-api.php';
            _twitter_api_init_l10n();
        }
        // We could cache the rendered HTML right here, but this keeps caching abstracted in library
        twitter_api_enable_cache( 300 );
        // Build API params for "statuses/user_timeline" // https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
        $trim_user = true;
        $include_rts = ! empty($rts);
        $exclude_replies = empty($ats);
        $params = compact('count','exclude_replies','include_rts','trim_user','screen_name');
        if( $exclude_replies || ! $include_rts ){
            // Stripping tweets means we may get less than $count tweets.
            // there is no good way around this other than fetch extra and hope for the best
            $params['count'] *= 3;
        }
        $tweets = twitter_api_get('statuses/user_timeline', $params );
        if( isset($tweets[$count]) ){
            $tweets = array_slice( $tweets, 0, $count );
        }
        // render each tweet as a blocks of html for the widget list items
        $rendered = array();
        foreach( $tweets as $tweet ){
            extract( $tweet );
            $link = esc_html( 'http://twitter.com/'.$screen_name.'/status/'.$id_str);
            // render nice datetime, unless theme overrides with filter
            $date = apply_filters( 'latest_tweets_render_date', $created_at );
            if( $date === $created_at ){
                function_exists('twitter_api_relative_date') or twitter_api_include('utils');
                $date = esc_html( twitter_api_relative_date($created_at) );
                $date = '<time datetime="'.$created_at.'">'.$date.'</time>';
            }
            // render and linkify tweet, unless theme overrides with filter
            $html = apply_filters('latest_tweets_render_text', $text );
            if( $html === $text ){
                function_exists('twitter_api_html') or twitter_api_include('utils');
                $html = twitter_api_html( $text );
            }
            // piece together the whole tweet, allowing overide
            $final = apply_filters('latest_tweets_render_tweet', $html, $date, $link );
            if( $final === $html ){
                $final = '<p class="tweet-text">'.$html.'</p>'.
                         '<p class="tweet-details"><a href="'.$link.'" target="_blank">'.$date.'</a></p>';
            }
            $rendered[] = $final;
        }
        return $rendered;
    }
    catch( Exception $Ex ){
        return array( '<p class="tweet-text"><strong>Error:</strong> '.esc_html($Ex->getMessage()).'</p>' );
    }
} 


 
  
/**
 * latest tweets widget class
 */
class Latest_Tweets_Widget extends WP_Widget {
    
    /** @see WP_Widget::__construct */
    public function __construct( $id_base = false, $name = 'Latest Tweets', $widget_options = array(), $control_options = array() ){
        $this->options = array(
            array (
                'name'  => 'title',
                'label' => __('Widget title'),
                'type'  => 'text'
            ),
            array (
                'name'  => 'screen_name',
                'label' => __('Twitter handle'),
                'type'  => 'text'
            ),
            array (
                'name'  => 'num',
                'label' => __('Number of tweets'),
                'type'  => 'text'
            ),
            array (
                'name'  => 'rts',
                'label' => __('Show Retweets'),
                'type'  => 'bool'
            ),
            array (
                'name'  => 'ats',
                'label' => __('Show Replies'),
                'type'  => 'bool'
            ),
        );
        parent::__construct( $id_base, __($name), $widget_options, $control_options );  
    }    
    
    /* ensure no missing keys in instance params */
    private function check_instance( $instance ){
        if( ! is_array($instance) ){
            $instance = array();
        }
        $instance += array (
            'title' => __('Latest Tweets'),
            'screen_name' => '',
            'num' => '5',
            'rts' => '',
            'ats' => '',
        );
        return $instance;
    }
    
    /** @see WP_Widget::form */
    public function form( $instance ) {
        $instance = $this->check_instance( $instance );
        foreach ( $this->options as $val ) {
            $elmid = $this->get_field_id( $val['name'] );
            $fname = $this->get_field_name($val['name']);
            $value = isset($instance[ $val['name'] ]) ? $instance[ $val['name'] ] : '';
            $label = '<label for="'.$elmid.'">'.$val['label'].'</label>';
            if( 'bool' === $val['type'] ){
                 $checked = $value ? ' checked="checked"' : '';
                 echo '<p><input type="checkbox" value="1" id="'.$elmid.'" name="'.$fname.'"'.$checked.' /> '.$label.'</p>';
            }
            else {
                $attrs = '';
                echo '<p>'.$label.'<br /><input class="widefat" type="text" value="'.esc_attr($value).'" id="'.$elmid.'" name="'.$fname.'" /></p>';
            }
        }
    }

    /** @see WP_Widget::widget */
    public function widget( array $args, $instance ) {
        extract( $this->check_instance($instance) );
        // title is themed via Wordpress widget theming techniques
        $title = $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        // by default tweets are rendered as an unordered list
        $items = latest_tweets_render( $screen_name, $num, $rts, $ats );
        $list  = apply_filters('latest_tweets_render_list', $items );
        if( is_array($list) ){
            $list = '<ul><li>'.implode('</li><li>',$items).'</li></ul>';
        }
        // output widget applying filters to each element
        echo 
        $args['before_widget'], 
            $title,
            '<div class="latest-tweets">', 
                apply_filters( 'latest_tweets_render_before', '' ),
                $list,
                apply_filters( 'latest_tweets_render_after', '' ),
            '</div>',
         $args['after_widget'];
    }
    
}
 


function latest_tweets_register_widget(){
    return register_widget('Latest_Tweets_Widget');
}

add_action( 'widgets_init', 'latest_tweets_register_widget' );


if( is_admin() ){
    require_once dirname(__FILE__).'/lib/twitter-api.php';
    
    // extra visibility of API settings link
    function latest_tweets_plugin_row_meta( $links, $file ){
        if( false !== strpos($file,'/latest-tweets.php') ){
            $links[] = '<a href="options-general.php?page=twitter-api-admin"><strong>'.esc_attr__('Connect to Twitter').'</strong></a>';
        } 
        return $links;
    }
    add_action('plugin_row_meta', 'latest_tweets_plugin_row_meta', 10, 2 );
}

