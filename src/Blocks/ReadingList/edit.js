import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
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
                            'Leave empty for all categories.',
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
                    <SelectControl
                        label={__('Status', 'lektrail-reading-tracker')}
                        value={attributes.status || ''}
                        options={[
                            {
                                label: __('All', 'lektrail-reading-tracker'),
                                value: '',
                            },
                            {
                                label: __('Viewed', 'lektrail-reading-tracker'),
                                value: 'viewed',
                            },
                            {
                                label: __('Read', 'lektrail-reading-tracker'),
                                value: 'read',
                            },
                        ]}
                        onChange={(value) =>
                            setAttributes({ status: value || undefined })
                        }
                    />
                </PanelBody>
            </InspectorControls>

            <div {...useBlockProps()}>
                <ServerSideRender
                    block="lektrail/reading-list"
                    attributes={attributes}
                />
            </div>
        </>
    );
}
