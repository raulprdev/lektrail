<?php defined('ABSPATH') || exit; ?>
<style>
    .completionist-settings .postbox .hndle {
        padding: 10px 12px;
        margin: 0;
    }
    .completionist-settings .postbox .inside {
        padding: 0 12px 12px;
        margin: 0;
    }
</style>
<div class="wrap completionist-settings">
    <h1><?= esc_html__('Completionist Settings', 'completionist') ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields(\Completionist\AdminPage::MENU_SLUG); ?>

        <div class="postbox">
            <h2 class="hndle"><?= esc_html__('General', 'completionist') ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?= esc_html__('Post Types to Track', 'completionist') ?></th>
                        <td>
                            <?php foreach (get_post_types(['public' => true], 'objects') as $postType): ?>
                                <label>
                                    <input type="checkbox"
                                           name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[post_types][]"
                                           value="<?= esc_attr($postType->name) ?>"
                                           <?php checked(in_array($postType->name, $settings->postTypes())); ?>>
                                    <?= esc_html($postType->label) ?>
                                </label><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Privacy Notice', 'completionist') ?></th>
                        <td>
                            <textarea name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[privacy_notice]"
                                      rows="3" class="large-text"><?= esc_textarea($settings->privacyNotice()) ?></textarea>
                            <p class="description"><?= esc_html__('Displayed to users. Explains that reading progress is tracked in local storage.', 'completionist') ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?= esc_html__('Consent Settings', 'completionist') ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?= esc_html__('Require Consent', 'completionist') ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[require_consent]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[require_consent]"
                                       value="1"
                                       <?php checked($settings->requireConsent()); ?>>
                                <?= esc_html__('Ask users for consent before tracking', 'completionist') ?>
                            </label>
                            <p class="description"><?= esc_html__('If enabled, users must opt-in before their reading progress is tracked.', 'completionist') ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Consent Message', 'completionist') ?></th>
                        <td>
                            <textarea name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[consent_message]"
                                      rows="2" class="large-text"><?= esc_textarea($settings->consentMessage()) ?></textarea>
                            <p class="description"><?= esc_html__('Message shown to users when asking for consent.', 'completionist') ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Checkbox Label', 'completionist') ?></th>
                        <td>
                            <input type="text"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[consent_checkbox_label]"
                                   value="<?= esc_attr($settings->consentCheckboxLabel()) ?>"
                                   class="regular-text">
                            <p class="description"><?= esc_html__('Label for the consent checkbox.', 'completionist') ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?= esc_html__('Viewed Section', 'completionist') ?></h2>
            <div class="inside">
                <p class="description"><?= esc_html__('Posts the visitor started but did not scroll to the end.', 'completionist') ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?= esc_html__('Enable', 'completionist') ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[viewed_enabled]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[viewed_enabled]"
                                       value="1"
                                       <?php checked($settings->viewedEnabled()); ?>>
                                <?= esc_html__('Show this section in the widget', 'completionist') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Max Posts', 'completionist') ?></th>
                        <td>
                            <input type="number"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[max_viewed]"
                                   value="<?= esc_attr($settings->maxViewed()) ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Label', 'completionist') ?></th>
                        <td>
                            <input type="text"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[label_continue]"
                                   value="<?= esc_attr($settings->labelContinue()) ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?= esc_html__('Completed Section', 'completionist') ?></h2>
            <div class="inside">
                <p class="description"><?= esc_html__('Posts the visitor scrolled to the end.', 'completionist') ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?= esc_html__('Enable', 'completionist') ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[completed_enabled]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[completed_enabled]"
                                       value="1"
                                       <?php checked($settings->completedEnabled()); ?>>
                                <?= esc_html__('Show this section in the widget', 'completionist') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Max Posts', 'completionist') ?></th>
                        <td>
                            <input type="number"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[max_read]"
                                   value="<?= esc_attr($settings->maxRead()) ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Label', 'completionist') ?></th>
                        <td>
                            <input type="text"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[label_completed]"
                                   value="<?= esc_attr($settings->labelCompleted()) ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?= esc_html__('Suggestions Section', 'completionist') ?></h2>
            <div class="inside">
                <p class="description"><?= esc_html__('Posts the visitor has not seen yet.', 'completionist') ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?= esc_html__('Max Posts', 'completionist') ?></th>
                        <td>
                            <input type="number"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[max_suggestions]"
                                   value="<?= esc_attr($settings->maxSuggestions()) ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Label', 'completionist') ?></th>
                        <td>
                            <input type="text"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[label_suggestions]"
                                   value="<?= esc_attr($settings->labelSuggestions()) ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Cache Duration', 'completionist') ?></th>
                        <td>
                            <input type="number"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[suggestions_cache_hours]"
                                   value="<?= esc_attr($settings->suggestionsCacheHours()) ?>"
                                   min="1" max="168" class="small-text">
                            <?= esc_html__('hours', 'completionist') ?>
                            <p class="description"><?= esc_html__('How long to cache suggestions before refreshing. Also refreshes when you complete reading a post.', 'completionist') ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?= esc_html__('Other Labels', 'completionist') ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?= esc_html__('Empty State', 'completionist') ?></th>
                        <td>
                            <input type="text"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[label_empty]"
                                   value="<?= esc_attr($settings->labelEmpty()) ?>"
                                   class="regular-text">
                            <p class="description"><?= esc_html__('Shown when there is nothing to display.', 'completionist') ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Loading', 'completionist') ?></th>
                        <td>
                            <input type="text"
                                   name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[label_loading]"
                                   value="<?= esc_attr($settings->labelLoading()) ?>"
                                   class="regular-text">
                            <p class="description"><?= esc_html__('Shown while fetching suggestions.', 'completionist') ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>
</div>