/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	// const [placeholder, setPlaceholder] = useState('Insert Placeholder');
	const {title, placeholder, showTitle} = attributes;
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wp-llm' ) }>
					<ToggleControl
						checked={ !! showTitle }
						label={ __(
							'Show title',
							'wp-llm'
						) }
						onChange={ () =>
							setAttributes( {
								showTitle: ! showTitle,
							} )
						}
					/>
                    { showTitle && <TextControl
                        __nextHasNoMarginBottom
						__next40pxDefaultSize
                        label={ __( 'Title', 'wp-llm' ) }
                        value={ title }
                        onChange={ ( value ) => setAttributes( { title: value } ) }
                    /> }
					<TextControl
                        __nextHasNoMarginBottom
						__next40pxDefaultSize
                        label={ __( 'Placeholder', 'wp-llm' ) }
                        value={ placeholder }
                        onChange={ ( value ) => setAttributes( { placeholder: value } ) }
                    />
                </PanelBody>
            </InspectorControls>
			{showTitle && <p>{title}</p>}
			<p { ...useBlockProps() }>{ __( placeholder, 'wp-llm' ) }</p>
			{/* <p><input { ...useBlockProps() } className='wp-llm-input' type="text" /></p> */}
		</>
	);
}
