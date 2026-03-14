import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { pages } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

export default function Edit() {
    return (
        <div {...useBlockProps()}>
            <Placeholder
                icon={pages}
                label={__('Completionist Widget', 'completionist')}
                instructions={__(
                    'Displays reading progress and suggestions. Configure in Settings > Completionist.',
                    'completionist'
                )}
            />
        </div>
    );
}