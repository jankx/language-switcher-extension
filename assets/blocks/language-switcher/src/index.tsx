import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls
} from '@wordpress/block-editor';
import {
    PanelBody,
    PanelRow,
    ToggleControl,
    SelectControl,
    TextControl,
    BaseControl,
    __experimentalText as Text
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';
import metadata from './block.json';

interface Language {
    code: string;
    name: string;
    url: string;
    flag: string;
    current: boolean;
}

interface LanguageSwitcherAttributes {
    showFlags: boolean;
    showNames: boolean;
    showCurrent: boolean;
    displayType: 'dropdown' | 'list';
    displayMode: 'text' | 'icon_only' | 'icon_text';
    iconPosition: 'left' | 'right';
    languageIcons: Record<string, string>;
    className?: string;
}

interface LanguageSwitcherEditProps {
    attributes: LanguageSwitcherAttributes;
    setAttributes: (attributes: Partial<LanguageSwitcherAttributes>) => void;
}

function LanguageSwitcherEdit({ attributes, setAttributes }: LanguageSwitcherEditProps): JSX.Element {
    const {
        showFlags,
        showNames,
        showCurrent,
        displayType,
        displayMode,
        iconPosition,
        languageIcons,
        className
    } = attributes;

    const [languages, setLanguages] = useState<Language[]>([]);
    const [isLoading, setIsLoading] = useState<boolean>(true);

    const blockProps = useBlockProps({
        className: `language-switcher-block ls-mode-${displayMode || 'text'} ${displayMode === 'icon_text' ? `ls-icon-pos-${iconPosition || 'left'}` : ''} ${className || ''}`
    });

    useEffect(() => {
        const fetchLanguages = async (): Promise<void> => {
            try {
                setIsLoading(true);
                const response = await apiFetch<Language[]>({
                    path: '/jankx/v1/languages',
                    method: 'GET'
                });

                if (response && Array.isArray(response)) {
                    setLanguages(response);
                } else {
                    setLanguages([]);
                }
            } catch (err) {
                console.error('Failed to fetch languages:', err);
                setLanguages([]);
            } finally {
                setIsLoading(false);
            }
        };

        fetchLanguages();
    }, []);

    const showIconOptions = displayMode === 'icon_only' || displayMode === 'icon_text';

    const updateLanguageIcon = (code: string, svg: string): void => {
        const newIcons = { ...(languageIcons || {}) };
        if (svg.trim() === '') {
            delete newIcons[code];
        } else {
            newIcons[code] = svg;
        }
        setAttributes({ languageIcons: newIcons });
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'jankx')} initialOpen={true}>
                    <SelectControl
                        label={__('Display Type', 'jankx')}
                        value={displayType}
                        options={[
                            { label: __('Dropdown', 'jankx'), value: 'dropdown' },
                            { label: __('List', 'jankx'), value: 'list' }
                        ]}
                        onChange={(value: string) => setAttributes({ displayType: value as 'dropdown' | 'list' })}
                    />

                    <SelectControl
                        label={__('Display Mode', 'jankx')}
                        value={displayMode}
                        options={[
                            { label: __('Text', 'jankx'), value: 'text' },
                            { label: __('Icon Only', 'jankx'), value: 'icon_only' },
                            { label: __('Icon + Text', 'jankx'), value: 'icon_text' }
                        ]}
                        onChange={(value: string) => setAttributes({ displayMode: value as 'text' | 'icon_only' | 'icon_text' })}
                        help={__('Choose what to display for each language', 'jankx')}
                    />

                    {displayMode === 'icon_text' && (
                        <SelectControl
                            label={__('Icon Position', 'jankx')}
                            value={iconPosition}
                            options={[
                                { label: __('Left', 'jankx'), value: 'left' },
                                { label: __('Right', 'jankx'), value: 'right' }
                            ]}
                            onChange={(value: string) => setAttributes({ iconPosition: value as 'left' | 'right' })}
                            help={__('Position of icon relative to text', 'jankx')}
                        />
                    )}

                    <ToggleControl
                        label={__('Show Current Language', 'jankx')}
                        checked={showCurrent}
                        onChange={(value: boolean) => setAttributes({ showCurrent: value })}
                        help={__('Include current language in the switcher', 'jankx')}
                    />
                </PanelBody>

                {showIconOptions && languages.length > 0 && (
                    <PanelBody title={__('Custom Icons (SVG)', 'jankx')} initialOpen={false}>
                        <Text variant="muted" size="small">
                            {__('Paste custom SVG for each language. Leave empty to use Polylang flag.', 'jankx')}
                        </Text>
                        {languages.map((lang) => (
                            <BaseControl
                                key={lang.code}
                                label={`${lang.name} (${lang.code.toUpperCase()})`}
                                className="language-icon-input"
                            >
                                <textarea
                                    value={languageIcons?.[lang.code] || ''}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => updateLanguageIcon(lang.code, e.target.value)}
                                    placeholder={lang.flag ? __('Uses Polylang flag', 'jankx') : __('No flag available', 'jankx')}
                                    rows={3}
                                    style={{ width: '100%', fontFamily: 'monospace', fontSize: '11px' }}
                                />
                                {languageIcons?.[lang.code] && (
                                    <div
                                        className="language-icon-preview"
                                        style={{ marginTop: '4px', display: 'flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span>{__('Preview:', 'jankx')}</span>
                                        <span
                                            dangerouslySetInnerHTML={{ __html: languageIcons[lang.code] }}
                                            style={{ display: 'inline-flex', alignItems: 'center' }}
                                        />
                                    </div>
                                )}
                            </BaseControl>
                        ))}
                    </PanelBody>
                )}
            </InspectorControls>

            <div {...blockProps}>
                <ServerSideRender
                    block="jankx/language-switcher"
                    attributes={attributes}
                />
            </div>
        </>
    );
}

function LanguageSwitcherSave(): null {
    return null;
}

registerBlockType(metadata.name, {
    ...metadata,
    edit: LanguageSwitcherEdit,
    save: LanguageSwitcherSave,
});
