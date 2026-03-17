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
    <h1><?php echo esc_html__('Completionist Settings', 'completionist'); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields(\Completionist\AdminPage::MENU_SLUG); ?>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('General', 'completionist'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Post Types to Track', 'completionist'); ?></th>
                        <td>
                            <?php foreach (get_post_types(['public' => true], 'objects') as $completionist_post_type): ?>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[post_types][]"
                                           value="<?php echo esc_attr($completionist_post_type->name); ?>"
                                           <?php checked(in_array($completionist_post_type->name, $pluginConfig->postTypes())); ?>>
                                    <?php echo esc_html($completionist_post_type->label); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Track Logged-In Users', 'completionist'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[track_logged_in_users]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[track_logged_in_users]"
                                       value="1"
                                       <?php checked($pluginConfig->trackLoggedInUsers()); ?>>
                                <?php echo esc_html__('Store reading history in database for logged-in users', 'completionist'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('Useful for membership sites. Stores reading history in database for WordPress users (admins, members). Most sites without public registration can leave this disabled.', 'completionist'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Display', 'completionist'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Control what information is shown for each post in the widget.', 'completionist'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Show Excerpt', 'completionist'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_excerpt]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_excerpt]"
                                       value="1"
                                       <?php checked($pluginConfig->showExcerpt()); ?>>
                                <?php echo esc_html__('Display post excerpt below the title', 'completionist'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Show Thumbnail', 'completionist'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_thumbnail]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_thumbnail]"
                                       value="1"
                                       <?php checked($pluginConfig->showThumbnail()); ?>>
                                <?php echo esc_html__('Display post thumbnail image', 'completionist'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Excerpt Length', 'completionist'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[excerpt_length]"
                                   value="<?php echo esc_attr($pluginConfig->excerptLength()); ?>"
                                   min="5" max="100" class="small-text">
                            <?php echo esc_html__('words', 'completionist'); ?>
                            <p class="description"><?php echo esc_html__('Number of words to show in excerpt.', 'completionist'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Consent Settings', 'completionist'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Require Consent', 'completionist'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[require_consent]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[require_consent]"
                                       value="1"
                                       <?php checked($pluginConfig->requireConsent()); ?>>
                                <?php echo esc_html__('Ask users for consent before tracking', 'completionist'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('If enabled, users must opt-in before their reading progress is tracked.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Consent Message', 'completionist'); ?></th>
                        <td>
                            <textarea name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[consent_message]"
                                      rows="2" class="large-text"><?php echo esc_textarea($pluginConfig->consentMessage()); ?></textarea>
                            <p class="description"><?php echo esc_html__('Message shown to users when asking for consent.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Checkbox Label', 'completionist'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[consent_checkbox_label]"
                                   value="<?php echo esc_attr($pluginConfig->consentCheckboxLabel()); ?>"
                                   class="regular-text">
                            <p class="description"><?php echo esc_html__('Label for the consent checkbox.', 'completionist'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Viewed Section', 'completionist'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Posts the visitor started but did not scroll to the end.', 'completionist'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable', 'completionist'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[viewed_enabled]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[viewed_enabled]"
                                       value="1"
                                       <?php checked($pluginConfig->viewedEnabled()); ?>>
                                <?php echo esc_html__('Show this section in the widget', 'completionist'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Max Posts', 'completionist'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[max_viewed]"
                                   value="<?php echo esc_attr($pluginConfig->maxViewed()); ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Label', 'completionist'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_continue]"
                                   value="<?php echo esc_attr($pluginConfig->labelContinue()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Completed Section', 'completionist'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Posts the visitor scrolled to the end.', 'completionist'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable', 'completionist'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[completed_enabled]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[completed_enabled]"
                                       value="1"
                                       <?php checked($pluginConfig->completedEnabled()); ?>>
                                <?php echo esc_html__('Show this section in the widget', 'completionist'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Read Threshold', 'completionist'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[read_threshold]"
                                   value="<?php echo esc_attr($pluginConfig->readThreshold()); ?>"
                                   min="10" max="100" class="small-text">
                            <?php echo esc_html('%', 'completionist'); ?>
                            <p class="description"><?php echo esc_html__('How far the user must scroll to mark the post as completed. Use lower values (e.g., 10%) for product pages where viewing is enough.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Max Posts', 'completionist'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[max_read]"
                                   value="<?php echo esc_attr($pluginConfig->maxRead()); ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Label', 'completionist'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_completed]"
                                   value="<?php echo esc_attr($pluginConfig->labelCompleted()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Suggestions Section', 'completionist'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Posts the visitor has not seen yet.', 'completionist'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Max Posts', 'completionist'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[max_suggestions]"
                                   value="<?php echo esc_attr($pluginConfig->maxSuggestions()); ?>"
                                   min="1" max="20" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Label', 'completionist'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_suggestions]"
                                   value="<?php echo esc_attr($pluginConfig->labelSuggestions()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Cache Duration', 'completionist'); ?></th>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[suggestions_cache_hours]"
                                   value="<?php echo esc_attr($pluginConfig->suggestionsCacheHours()); ?>"
                                   min="1" max="168" class="small-text">
                            <?php echo esc_html__('hours', 'completionist'); ?>
                            <p class="description"><?php echo esc_html__('How long to cache suggestions before refreshing. Also refreshes when you complete reading a post.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Order', 'completionist'); ?></th>
                        <td>
                            <select name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[suggestion_order]">
                                <option value="random" <?php selected($pluginConfig->suggestionOrder(), 'random'); ?>>
                                    <?php echo esc_html__('Random', 'completionist'); ?>
                                </option>
                                <option value="recent" <?php selected($pluginConfig->suggestionOrder(), 'recent'); ?>>
                                    <?php echo esc_html__('Recent (newest first)', 'completionist'); ?>
                                </option>
                                <option value="related" <?php selected($pluginConfig->suggestionOrder(), 'related'); ?>>
                                    <?php echo esc_html__('Related (same categories as read posts)', 'completionist'); ?>
                                </option>
                            </select>
                            <p class="description"><?php echo esc_html__('How to order suggested posts.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <?php $completionist_categories = get_categories(['hide_empty' => false]); ?>
                    <?php if (!empty($completionist_categories)): ?>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Include Categories', 'completionist'); ?></th>
                        <td>
                            <fieldset>
                                <?php foreach ($completionist_categories as $completionist_category): ?>
                                    <label>
                                        <input type="checkbox"
                                               name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[include_categories][]"
                                               value="<?php echo esc_attr($completionist_category->term_id); ?>"
                                               <?php checked(in_array($completionist_category->term_id, $pluginConfig->includeCategories())); ?>>
                                        <?php echo esc_html($completionist_category->name); ?>
                                    </label><br>
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="description"><?php echo esc_html__('Only suggest posts from these categories. Leave empty to include all. Ignored when using "Related" order.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Exclude Categories', 'completionist'); ?></th>
                        <td>
                            <fieldset>
                                <?php foreach ($completionist_categories as $completionist_category): ?>
                                    <label>
                                        <input type="checkbox"
                                               name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[exclude_categories][]"
                                               value="<?php echo esc_attr($completionist_category->term_id); ?>"
                                               <?php checked(in_array($completionist_category->term_id, $pluginConfig->excludeCategories())); ?>>
                                        <?php echo esc_html($completionist_category->name); ?>
                                    </label><br>
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="description"><?php echo esc_html__('Never suggest posts from these categories. Ignored when using "Related" order.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Other Labels', 'completionist'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Empty State', 'completionist'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_empty]"
                                   value="<?php echo esc_attr($pluginConfig->labelEmpty()); ?>"
                                   class="regular-text">
                            <p class="description"><?php echo esc_html__('Shown when there is nothing to display.', 'completionist'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Loading', 'completionist'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_loading]"
                                   value="<?php echo esc_attr($pluginConfig->labelLoading()); ?>"
                                   class="regular-text">
                            <p class="description"><?php echo esc_html__('Shown while fetching suggestions.', 'completionist'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><?php echo esc_html__('Clear Data', 'completionist'); ?></h2>
            <div class="inside">
                <p class="description"><?php echo esc_html__('Allow users to clear their reading history.', 'completionist'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Show Clear Button', 'completionist'); ?></th>
                        <td>
                            <input type="hidden"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_clear_button]"
                                   value="0">
                            <label>
                                <input type="checkbox"
                                       name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[show_clear_button]"
                                       value="1"
                                       <?php checked($pluginConfig->showClearButton()); ?>>
                                <?php echo esc_html__('Display a button to clear reading history (stored in browser)', 'completionist'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Button Label', 'completionist'); ?></th>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr(\Completionist\WordPress\PluginConfigRepository::OPTION_KEY); ?>[label_clear]"
                                   value="<?php echo esc_attr($pluginConfig->labelClear()); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>
</div>