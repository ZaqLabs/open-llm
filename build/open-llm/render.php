<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

?>
<p><?php echo esc_html( $attributes['title'] ); ?></p>
<form id="open-llm-form" onsubmit="event.preventDefault();">
    <p><textarea <?php echo esc_attr(get_block_wrapper_attributes()); ?> placeholder="<?php echo esc_attr($attributes['placeholder']); ?>" id="open-llm-textarea"></textarea></p>
    <p id="open-llm-loading" style="display: none;">
        Processing...
    </p>
    <p><input class="wp-button-open-llm" type="submit" value="Submit"></p>
</form>