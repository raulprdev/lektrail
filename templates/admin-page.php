<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1>Completionist Settings</h1>

    <form method="post" action="options.php">
        <?php settings_fields(\Completionist\AdminPage::MENU_SLUG); ?>

        <table class="form-table">
            <tr>
                <th scope="row">Post Types to Track</th>
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
                <th scope="row">Max "Continue Reading" Posts</th>
                <td>
                    <input type="number"
                           name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[max_viewed]"
                           value="<?= esc_attr($settings->maxViewed()) ?>"
                           min="1" max="20" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Max "Completed" Posts</th>
                <td>
                    <input type="number"
                           name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[max_read]"
                           value="<?= esc_attr($settings->maxRead()) ?>"
                           min="1" max="20" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Max Suggestions</th>
                <td>
                    <input type="number"
                           name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[max_suggestions]"
                           value="<?= esc_attr($settings->maxSuggestions()) ?>"
                           min="1" max="20" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Privacy Notice</th>
                <td>
                    <textarea name="<?= \Completionist\WordPressSettingsRepository::OPTION_KEY ?>[privacy_notice]"
                              rows="3" class="large-text"><?= esc_textarea($settings->privacyNotice()) ?></textarea>
                    <p class="description">Displayed to users. Explains that reading progress is tracked in local storage.</p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>