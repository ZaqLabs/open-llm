<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

?>
<p><?php echo $attributes['title']; ?></p>
<form id="wp-llm-form" onsubmit="event.preventDefault();">
    <p><textarea <?php echo get_block_wrapper_attributes(); ?> placeholder="<?php echo $attributes['placeholder']; ?>" id="wp-llm-textarea"></textarea></p>
    <p id="wp-llm-loading" style="display: none;">
        Processing...
    </p>
    <p><input class="wp-button-wp-llm" type="submit" value="Submit"></p>
</form>