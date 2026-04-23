import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ComboboxControl,
	TextControl,
	SelectControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
	const categories = useSelect((select) =>
		select('core').getEntityRecords('taxonomy', 'category', {
			per_page: -1,
			orderby: 'name',
			order: 'asc',
		})
	);

	const categoryOptions = useMemo(() => {
		const options = [
			{
				label: __('All categories', 'lektrail-reading-tracker'),
				value: '',
			},
		];
		if (categories) {
			categories.forEach((cat) => {
				options.push({ label: cat.name, value: cat.slug });
			});
		}
		return options;
	}, [categories]);

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Filters', 'lektrail-reading-tracker')}
					initialOpen={true}
				>
					<ComboboxControl
						label={__('Category', 'lektrail-reading-tracker')}
						value={attributes.category || ''}
						options={categoryOptions}
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
