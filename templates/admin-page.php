<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1><?= esc_html__('Completionist Settings', 'completionist') ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields(\Completionist\AdminPage::MENU_SLUG); ?>

        <h2><?= esc_html__('General', 'completionist') ?></h2>
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

        <h2><?= esc_html__('Viewed Section', 'completionist') ?></h2>
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

        <h2><?= esc_html__('Completed Section', 'completionist') ?></h2>
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

        <h2><?= esc_html__('Suggestions Section', 'completionist') ?></h2>
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
        </table>

        <h2><?= esc_html__('Other Labels', 'completionist') ?></h2>
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

        <?php submit_button(); ?>
    </form>
</div>