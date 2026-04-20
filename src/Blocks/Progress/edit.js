import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
    return (
        <>
            <InspectorControls>
                <PanelBody
                    title={__('Filters', 'lektrail-reading-tracker')}
                    initialOpen={true}
                >
                    <TextControl
                        label={__('Category Slug', 'lektrail-reading-tracker')}
                        value={attributes.category || ''}
                        onChange={(value) =>
                            setAttributes({ category: value || undefined })
                        }
                        help={__(
                            'Leave empty for global progress.',
                            'lektrail-reading-tracker'
                        )}
                    />
                    <TextControl
                        label={__('Year', 'lektrail-reading-tracker')}
                        value={attributes.year || ''}
                        onChange={(value) =>
                            setAttributes({
                                year: value ? parseInt(value, 10) : undefined,
                            })
                        }
                        help={__(
                            'Filter by post publish year.',
                            'lektrail-reading-tracker'
                        )}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...useBlockProps()}>
                <ServerSideRender
                    block="lektrail/progress"
                    attributes={attributes}
                />
            </div>
        </>
    );
}
