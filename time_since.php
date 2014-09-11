<?php

class FacetWP_Facet_Time_Since
{

    function __construct() {
        $this->label = __( 'Time Since', 'fwp' );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $where_clause = $params['where_clause'];

        $sql = "
        SELECT DISTINCT f.facet_value
        FROM {$wpdb->prefix}facetwp_index f
        WHERE f.facet_name = '{$facet['name']}' $where_clause";
        $results = $wpdb->get_results( $sql );

        // Parse the "choices" setting
        $choices = explode( "\n", $facet['choices'] );
        foreach ( $choices as $key => $choice ) {
            $temp = array_map( 'trim', explode( '|', $choice ) );
            $choices[ $key ] = array(
                'label' => $temp[0],
                'format' => $temp[1],
                'seconds' => strtotime( $temp[1] ),
                'counter' => 0,
            );
        }

        // Loop through the results
        foreach ( $results as $result ) {
            $post_time = (int) strtotime( $result->facet_value );
            foreach ( $choices as $key => $choice ) {
                $choice_time = $choice['seconds'];

                // last week, etc.
                if ( $choice_time < time() && $post_time >= $choice_time ) {
                    $choices[ $key ]['counter']++;
                }
                // next week, etc.
                elseif ( $choice_time > time() && $post_time <= $choice_time ) {
                    $choices[ $key ]['counter']++;
                }
            }
        }

        // We need to return an array of objects
        // Each row having "facet_value", "facet_display_value", and "counter"
        $output = array();
        foreach ( $choices as $choice ) {
            if ( 0 < $choice['counter'] ) {
                $output[] = (object) array(
                    'facet_value' => $choice['seconds'],
                    'facet_display_value' => $choice['label'],
                    'counter' => $choice['counter'],
                );
            }
        }
        return $output;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        $is_empty = empty( $selected_values ) ? ' selected' : '';
        $output .= '<div class="facetwp-radio' . $is_empty  . '" data-value="">' . __( 'Any', 'fwp' ) . '</div>';

        foreach ( $values as $result ) {
            $selected = in_array( $result->facet_value, $selected_values ) ? ' selected' : '';

            // Determine whether to show counts
            $display_value = $result->facet_display_value;
            $display_value .= " <span class='counts'>($result->counter)</span>";
            $output .= '<div class="facetwp-radio' . $selected . '" data-value="' . $result->facet_value . '">' . $display_value . '</div>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;
        $selected_values = date( 'Y-m-d H:i:s', (int) $selected_values );

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND facet_value >= '$selected_values'";
        return $wpdb->get_col( $sql );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/time_since', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.type-time_since .facet-choices').val(obj.choices);
    });

    wp.hooks.addFilter('facetwp/save/time_since', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['choices'] = $this.find('.type-time_since .facet-choices').val();
        return obj;
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
?>

<link href="<?php echo WP_CONTENT_URL; ?>/plugins/facetwp-time-since/assets/css/front.css" rel="stylesheet">

<script>
(function($) {
    wp.hooks.addAction('facetwp/refresh/time_since', function($this, facet_name) {
        var selected_values = [];
        $this.find('.facetwp-radio.selected').each(function() {
            var val = $(this).attr('data-value');
            if ('' != val) {
                selected_values.push(val);
            }
        });
        FWP.facets[facet_name] = selected_values;
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('click', '.facetwp-radio', function() {
            var $facet = $(this).closest('.facetwp-facet');
            $facet.find('.facetwp-radio').removeClass('selected');
            $(this).addClass('selected');
            if ('' != $(this).attr('data-value')) {
                FWP.static_facet = $facet.attr('data-name');
            }
            FWP.autoload();
        });
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <tr class="facetwp-conditional type-time_since">
            <td>
                <?php _e('Choices', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Enter the available choices (one per line)', 'fwp' ); ?></div>
                </div>
            </td>
            <td><textarea class="facet-choices"></textarea></td>
        </tr>
<?php
    }
}
