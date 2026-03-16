import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RangeControl,
	SelectControl,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState, useMemo } from '@wordpress/element';
import { useDebounce } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';

const defaults = window.completionistDefaults;

/**
 * Build widget config for editor preview.
 * Structure must match PluginConfig::toJsConfig() in src/PluginConfig.php
 */
function buildConfig(attributes) {
	return {
		widgetId: 'completionist-editor-preview',
		maxViewed: attributes.maxViewed ?? defaults?.maxViewed ?? 3,
		maxRead: attributes.maxRead ?? defaults?.maxRead ?? 3,
		maxSuggestions: attributes.maxSuggestions ?? defaults?.maxSuggestions ?? 3,
		showExcerpt: attributes.showExcerpt ?? defaults?.showExcerpt ?? false,
		showThumbnail: attributes.showThumbnail ?? defaults?.showThumbnail ?? false,
		viewedEnabled: attributes.viewedEnabled ?? defaults?.viewedEnabled ?? true,
		completedEnabled: attributes.completedEnabled ?? defaults?.completedEnabled ?? true,
		showClearButton: false,
		requireConsent: false,
		serverSideTracking: true,
		labels: {
			continue: attributes.labelContinue ?? defaults?.labels?.continue ?? 'Continue reading',
			completed: attributes.labelCompleted ?? defaults?.labels?.completed ?? 'Completed',
			suggestions: attributes.labelSuggestions ?? defaults?.labels?.suggestions ?? 'Suggested reading',
			empty: attributes.labelEmpty ?? defaults?.labels?.empty ?? 'Start reading!',
			loading: attributes.labelLoading ?? defaults?.labels?.loading ?? 'Loading...',
			clear: attributes.labelClear ?? defaults?.labels?.clear ?? 'Clear history',
		},
	};
}

export default function Edit({ attributes, setAttributes }) {
	const containerRef = useRef(null);
	const [previewData, setPreviewData] = useState(null);
	const [isLoading, setIsLoading] = useState(true);

	const maxViewed = attributes.maxViewed ?? defaults?.maxViewed;
	const maxRead = attributes.maxRead ?? defaults?.maxRead;
	const maxSuggestions = attributes.maxSuggestions ?? defaults?.maxSuggestions;
	const showExcerpt = attributes.showExcerpt ?? defaults?.showExcerpt;
	const showThumbnail = attributes.showThumbnail ?? defaults?.showThumbnail;
	const excerptLength = attributes.excerptLength ?? defaults?.excerptLength;
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
	const suggestionsCacheHours =
		attributes.suggestionsCacheHours ?? defaults?.suggestionsCacheHours;

	const config = useMemo(() => buildConfig(attributes), [attributes]);

	const fetchPreview = useDebounce(() => {
		const params = new URLSearchParams({
			maxViewed: maxViewed ?? 3,
			maxRead: maxRead ?? 3,
			maxSuggestions: maxSuggestions ?? 3,
		});
		apiFetch({ path: `/completionist/v1/preview?${params}` })
			.then((data) => {
				setPreviewData(data);
				setIsLoading(false);
			})
			.catch(() => {
				setIsLoading(false);
			});
	}, 300);

	useEffect(() => {
		fetchPreview();
	}, [maxViewed, maxRead, maxSuggestions, fetchPreview]);

	const initWidget = useDebounce(() => {
		if (!containerRef.current || !previewData || !window.CompletionistWidget) {
			return;
		}
		window.CompletionistWidget.init(containerRef.current, previewData, config);
	}, 300);

	useEffect(() => {
		if (previewData) {
			initWidget();
		}
	}, [previewData, config, initWidget]);

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
							{
								label: __('Default (use global)', 'completionist'),
								value: '',
							},
							{ label: __('Random', 'completionist'), value: 'random' },
							{ label: __('Recent', 'completionist'), value: 'recent' },
							{ label: __('Related', 'completionist'), value: 'related' },
						]}
						onChange={(value) =>
							setAttributes({ suggestionOrder: value || undefined })
						}
					/>
				</PanelBody>

				<PanelBody
					title={__('Labels', 'completionist')}
					initialOpen={false}
				>
					<TextControl
						label={__('Continue Reading Label', 'completionist')}
						value={labelContinue || ''}
						onChange={(value) =>
							setAttributes({ labelContinue: value || undefined })
						}
						placeholder={__('Continue reading', 'completionist')}
					/>
					<TextControl
						label={__('Completed Label', 'completionist')}
						value={labelCompleted || ''}
						onChange={(value) =>
							setAttributes({ labelCompleted: value || undefined })
						}
						placeholder={__('Completed', 'completionist')}
					/>
					<TextControl
						label={__('Suggestions Label', 'completionist')}
						value={labelSuggestions || ''}
						onChange={(value) =>
							setAttributes({ labelSuggestions: value || undefined })
						}
						placeholder={__('Suggested reading', 'completionist')}
					/>
					<TextControl
						label={__('Empty State Label', 'completionist')}
						value={labelEmpty || ''}
						onChange={(value) =>
							setAttributes({ labelEmpty: value || undefined })
						}
						placeholder={__(
							'Start reading to track your progress!',
							'completionist'
						)}
					/>
					<TextControl
						label={__('Loading Label', 'completionist')}
						value={labelLoading || ''}
						onChange={(value) =>
							setAttributes({ labelLoading: value || undefined })
						}
						placeholder={__('Loading suggestions...', 'completionist')}
					/>
					<TextControl
						label={__('Clear Button Label', 'completionist')}
						value={labelClear || ''}
						onChange={(value) =>
							setAttributes({ labelClear: value || undefined })
						}
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
						onChange={(value) =>
							setAttributes({ suggestionsCacheHours: value })
						}
						min={1}
						max={168}
						allowReset
						resetFallbackValue={undefined}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()}>
				{isLoading ? (
					<div
						style={{
							display: 'flex',
							justifyContent: 'center',
							padding: '20px',
						}}
					>
						<Spinner />
					</div>
				) : (
					<div
						ref={containerRef}
						id="completionist-editor-preview"
						className="completionist-widget"
					/>
				)}
			</div>
		</>
	);
}