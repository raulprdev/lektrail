import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	Placeholder,
	PanelBody,
	TextControl,
	ToggleControl,
	RangeControl,
	SelectControl,
} from '@wordpress/components';
import { pages } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

const defaults = window.completionistDefaults;

export default function Edit({ attributes, setAttributes }) {
	const maxViewed = attributes.maxViewed ?? defaults?.maxViewed;
	const maxRead = attributes.maxRead ?? defaults?.maxRead;
	const maxSuggestions = attributes.maxSuggestions ?? defaults?.maxSuggestions;
	const showExcerpt = attributes.showExcerpt ?? defaults?.showExcerpt;
	const showThumbnail = attributes.showThumbnail ?? defaults?.showThumbnail;
	const excerptLength = attributes.excerptLength;
	const viewedEnabled = attributes.viewedEnabled ?? defaults?.viewedEnabled;
	const completedEnabled = attributes.completedEnabled ?? defaults?.completedEnabled;
	const showClearButton = attributes.showClearButton ?? defaults?.showClearButton;
	const labelContinue = attributes.labelContinue ?? defaults?.labels?.continue;
	const labelCompleted = attributes.labelCompleted ?? defaults?.labels?.completed;
	const labelSuggestions = attributes.labelSuggestions ?? defaults?.labels?.suggestions;
	const labelEmpty = attributes.labelEmpty ?? defaults?.labels?.empty;
	const labelLoading = attributes.labelLoading ?? defaults?.labels?.loading;
	const labelClear = attributes.labelClear ?? defaults?.labels?.clear;
	const suggestionOrder = attributes.suggestionOrder;
	const suggestionsCacheHours = attributes.suggestionsCacheHours ?? defaults?.suggestionsCacheHours;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__('Display Settings', 'completionist')}
					initialOpen={true}
				>
					<RangeControl
						label={__('Max Viewed Posts', 'completionist')}
						value={maxViewed}
						onChange={(value) => setAttributes({ maxViewed: value })}
						min={1}
						max={20}
						allowReset
						resetFallbackValue={undefined}
					/>
					<RangeControl
						label={__('Max Completed Posts', 'completionist')}
						value={maxRead}
						onChange={(value) => setAttributes({ maxRead: value })}
						min={1}
						max={20}
						allowReset
						resetFallbackValue={undefined}
					/>
					<RangeControl
						label={__('Max Suggestions', 'completionist')}
						value={maxSuggestions}
						onChange={(value) => setAttributes({ maxSuggestions: value })}
						min={1}
						max={20}
						allowReset
						resetFallbackValue={undefined}
					/>
					<ToggleControl
						label={__('Show Excerpts', 'completionist')}
						checked={showExcerpt}
						onChange={(value) => setAttributes({ showExcerpt: value })}
					/>
					<ToggleControl
						label={__('Show Thumbnails', 'completionist')}
						checked={showThumbnail}
						onChange={(value) => setAttributes({ showThumbnail: value })}
					/>
					{showExcerpt && (
						<RangeControl
							label={__('Excerpt Length (words)', 'completionist')}
							value={excerptLength}
							onChange={(value) => setAttributes({ excerptLength: value })}
							min={5}
							max={100}
							allowReset
							resetFallbackValue={undefined}
						/>
					)}
				</PanelBody>

				<PanelBody
					title={__('Sections', 'completionist')}
					initialOpen={false}
				>
					<ToggleControl
						label={__('Show Continue Reading', 'completionist')}
						checked={viewedEnabled}
						onChange={(value) => setAttributes({ viewedEnabled: value })}
					/>
					<ToggleControl
						label={__('Show Completed', 'completionist')}
						checked={completedEnabled}
						onChange={(value) => setAttributes({ completedEnabled: value })}
					/>
					<ToggleControl
						label={__('Show Clear Button', 'completionist')}
						checked={showClearButton}
						onChange={(value) => setAttributes({ showClearButton: value })}
					/>
					<SelectControl
						label={__('Suggestion Order', 'completionist')}
						value={suggestionOrder}
						options={[
							{ label: __('Default (use global)', 'completionist'), value: '' },
							{ label: __('Random', 'completionist'), value: 'random' },
							{ label: __('Recent', 'completionist'), value: 'recent' },
							{ label: __('Related', 'completionist'), value: 'related' },
						]}
						onChange={(value) => setAttributes({ suggestionOrder: value || undefined })}
					/>
				</PanelBody>

				<PanelBody
					title={__('Labels', 'completionist')}
					initialOpen={false}
				>
					<TextControl
						label={__('Continue Reading Label', 'completionist')}
						value={labelContinue || ''}
						onChange={(value) => setAttributes({ labelContinue: value || undefined })}
						placeholder={__('Continue reading', 'completionist')}
					/>
					<TextControl
						label={__('Completed Label', 'completionist')}
						value={labelCompleted || ''}
						onChange={(value) => setAttributes({ labelCompleted: value || undefined })}
						placeholder={__('Completed', 'completionist')}
					/>
					<TextControl
						label={__('Suggestions Label', 'completionist')}
						value={labelSuggestions || ''}
						onChange={(value) => setAttributes({ labelSuggestions: value || undefined })}
						placeholder={__('Suggested reading', 'completionist')}
					/>
					<TextControl
						label={__('Empty State Label', 'completionist')}
						value={labelEmpty || ''}
						onChange={(value) => setAttributes({ labelEmpty: value || undefined })}
						placeholder={__('Start reading to track your progress!', 'completionist')}
					/>
					<TextControl
						label={__('Loading Label', 'completionist')}
						value={labelLoading || ''}
						onChange={(value) => setAttributes({ labelLoading: value || undefined })}
						placeholder={__('Loading suggestions...', 'completionist')}
					/>
					<TextControl
						label={__('Clear Button Label', 'completionist')}
						value={labelClear || ''}
						onChange={(value) => setAttributes({ labelClear: value || undefined })}
						placeholder={__('Clear history', 'completionist')}
					/>
				</PanelBody>

				<PanelBody
					title={__('Performance', 'completionist')}
					initialOpen={false}
				>
					<RangeControl
						label={__('Suggestions Cache (hours)', 'completionist')}
						value={suggestionsCacheHours}
						onChange={(value) => setAttributes({ suggestionsCacheHours: value })}
						min={1}
						max={168}
						allowReset
						resetFallbackValue={undefined}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()}>
				<Placeholder
					icon={pages}
					label={__('Completionist Widget', 'completionist')}
					instructions={__(
						'Displays reading progress and suggestions. Use the sidebar to customize this instance.',
						'completionist'
					)}
				/>
			</div>
		</>
	);
}